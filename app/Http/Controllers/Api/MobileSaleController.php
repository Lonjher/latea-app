<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Sale;
use App\Services\InvoiceNumberService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MobileSaleController extends Controller
{
    /**
     * Submit transaksi baru dari kasir.
     */
    public function store(Request $request, InvoiceNumberService $invoiceService)
    {
        $validated = $request->validate([
            'items'              => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity'   => ['required', 'integer', 'min:1'],
            'payment_amount'     => ['required', 'numeric', 'min:0'],
        ]);

        $user    = $request->user();
        $storeId = $user->store_id;

        if (! $storeId) {
            return response()->json([
                'message' => 'Kasir belum ditugaskan ke toko manapun.',
            ], 403);
        }

        return DB::transaction(function () use ($validated, $user, $storeId, $invoiceService) {
            $subtotal       = 0;
            $total          = 0;
            $itemsData      = [];

            foreach ($validated['items'] as $item) {
                $product = Product::with([
                    'stores' => fn ($q) => $q->where('stores.id', $storeId)
                        ->withPivot(['price', 'is_available']),
                ])->find($item['product_id']);

                if (! $product) {
                    throw ValidationException::withMessages([
                        'items' => ["Produk ID {$item['product_id']} tidak ditemukan."],
                    ]);
                }

                // Validasi produk tersedia di store ini
                $pivot = $product->stores->first()?->pivot;
                if (! $pivot || ! $pivot->is_available) {
                    throw ValidationException::withMessages([
                        'items' => ["Produk {$product->name} tidak tersedia di toko Anda."],
                    ]);
                }

                // Harga dasar (pakai harga khusus toko jika ada)
                $basePrice = $pivot->price !== null
                    ? (float) $pivot->price
                    : (float) $product->price;

                // Harga diskon dari produk (jika ada)
                $discountPrice   = $product->discount_price !== null
                    ? (float) $product->discount_price
                    : null;
                $minimalDiscount = $product->minimal_discount;

                // ⭐ Hitung harga efektif — SAMA dengan logika di mobile
                $qty             = (int) $item['quantity'];
                $effectivePrice  = $basePrice;

                if (
                    $discountPrice !== null
                    && $minimalDiscount !== null
                    && $qty >= $minimalDiscount
                ) {
                    $effectivePrice = $discountPrice;
                }

                $lineTotal = $effectivePrice * $qty;

                $subtotal += $basePrice * $qty;
                $total    += $lineTotal;

                // Snapshot data produk
                $itemsData[] = [
                    'product_id'      => $product->id,
                    'product_name'    => $product->name,
                    'product_code'    => $product->code,
                    'price'           => $basePrice,
                    'discount_price'  => $discountPrice,
                    'effective_price' => $effectivePrice,
                    'quantity'        => $qty,
                    'line_total'      => $lineTotal,
                ];
            }

            $discountAmount = $subtotal - $total;
            $paymentAmount  = (float) $validated['payment_amount'];

            if ($paymentAmount < $total) {
                throw ValidationException::withMessages([
                    'payment_amount' => ['Uang diterima kurang dari total transaksi.'],
                ]);
            }

            $changeAmount = $paymentAmount - $total;

            // Buat sale header
            $sale = Sale::create([
                'store_id'        => $storeId,
                'cashier_id'      => $user->id,
                'cashier_name'    => $user->name,
                'invoice_number'  => $invoiceService->generate(),
                'sale_date'       => now(),
                'subtotal'        => $subtotal,
                'discount_amount' => $discountAmount,
                'tax_amount'      => 0,
                'total'           => $total,
                'payment_amount'  => $paymentAmount,
                'change_amount'   => $changeAmount,
                'status'          => 'completed',
            ]);

            // Insert sale items
            $sale->items()->createMany($itemsData);

            return response()->json([
                'data' => $this->salePayload($sale->fresh(['items', 'store'])),
            ], 201);
        });
    }

    /**
     * Riwayat transaksi kasir yang sedang login.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $sales = Sale::query()
            ->where('cashier_id', $user->id)
            ->withCount('items')
            ->withSum('items as total_quantity', 'quantity')   // ⭐ BARU
            ->when($request->date_from, fn ($q) => $q->whereDate('sale_date', '>=', $request->date_from))
            ->when($request->date_to, fn ($q) => $q->whereDate('sale_date', '<=', $request->date_to))
            ->orderByDesc('sale_date')
            ->paginate(20);

        return response()->json([
            'data' => $sales->map(fn (Sale $sale) => [
                'id'              => $sale->id,
                'invoice_number'  => $sale->invoice_number,
                'sale_date'       => $sale->sale_date->toIso8601String(),
                'total'           => (float) $sale->total,
                'items_count'     => $sale->items_count,           // tetap ada (jenis produk)
                'total_quantity'  => (int) ($sale->total_quantity ?? 0),  // ⭐ BARU (total qty)
                'status'          => $sale->status,
            ]),
            'meta' => [
                'current_page' => $sales->currentPage(),
                'last_page'    => $sales->lastPage(),
                'per_page'     => $sales->perPage(),
                'total'        => $sales->total(),
            ],
        ]);
    }

    /**
     * Detail struk transaksi.
     */
    public function show(Request $request, Sale $sale)
    {
        if ($sale->cashier_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Anda tidak memiliki akses ke transaksi ini.',
            ], 403);
        }

        return response()->json([
            'data' => $this->salePayload($sale->load(['items', 'store'])),
        ]);
    }

    /**
     * Format payload sale untuk mobile.
     */
    private function salePayload(Sale $sale): array
    {
        return [
            'id'              => $sale->id,
            'invoice_number'  => $sale->invoice_number,
            'sale_date'       => $sale->sale_date->toIso8601String(),
            'cashier_name'    => $sale->cashier_name,
            'store'           => $sale->store ? [
                'id'       => $sale->store->id,
                'name'     => $sale->store->name,
                'code'     => $sale->store->code,
                'location' => $sale->store->location,
            ] : null,
            'items'           => $sale->items->map(fn ($item) => [
                'product_id'      => $item->product_id,
                'product_name'    => $item->product_name,
                'product_code'    => $item->product_code,
                'price'           => (float) $item->price,
                'discount_price'  => $item->discount_price !== null
                    ? (float) $item->discount_price
                    : null,
                'effective_price' => (float) $item->effective_price,
                'quantity'        => $item->quantity,
                'line_total'      => (float) $item->line_total,
            ]),
            'subtotal'        => (float) $sale->subtotal,
            'discount_amount' => (float) $sale->discount_amount,
            'tax_amount'      => (float) $sale->tax_amount,
            'total'           => (float) $sale->total,
            'payment_amount'  => (float) $sale->payment_amount,
            'change_amount'   => (float) $sale->change_amount,
            'status'          => $sale->status,
        ];
    }

    /**
     * Ringkasan jumlah terjual per produk (per hari / range tanggal).
     */
    public function productSummary(Request $request)
    {
        $user = $request->user();

        $dateFrom = $request->date_from ?? now()->toDateString();
        $dateTo   = $request->date_to ?? now()->toDateString();

        $rows = DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->where('sales.cashier_id', $user->id)
            ->where('sales.status', 'completed')
            ->whereDate('sales.sale_date', '>=', $dateFrom)
            ->whereDate('sales.sale_date', '<=', $dateTo)
            ->selectRaw('
                sale_items.product_id,
                sale_items.product_name,
                sale_items.product_code,
                SUM(sale_items.quantity) as total_qty,
                SUM(sale_items.line_total) as total_revenue
            ')
            ->groupBy(
                'sale_items.product_id',
                'sale_items.product_name',
                'sale_items.product_code'
            )
            ->orderByDesc('total_qty')
            ->get();

        return response()->json(['data' => $rows]);
    }

}
