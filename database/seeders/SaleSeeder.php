<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SaleSeeder extends Seeder
{
    public function run(): void
    {
        $stores = Store::all();
        $products = Product::all();

        if ($stores->isEmpty() || $products->isEmpty()) {
            $this->command->warn('Butuh minimal 1 store dan 1 product. Jalankan StoreSeeder & ProductSeeder dulu.');
            return;
        }

        // Buat 30 transaksi
        for ($i = 1; $i <= 30; $i++) {
            $store = $stores->random();
            $cashier = User::where('store_id', $store->id)->inRandomOrder()->first()
                ?? User::inRandomOrder()->first();

            $saleDate = now()
                ->subDays(rand(0, 30))
                ->setTime(rand(8, 21), rand(0, 59), rand(0, 59));

            // Pilih 1-5 produk random
            $itemCount = rand(1, 5);
            $selectedProducts = $products->random(min($itemCount, $products->count()));

            $subtotal = 0;
            $items = [];

            foreach ($selectedProducts as $product) {
                $quantity = rand(1, 10);

                $effectivePrice = $product->effectivePriceFor($quantity);

                $lineTotal = $effectivePrice * $quantity;
                $subtotal += $lineTotal;

                $items[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_code' => $product->code,
                    'price' => $product->price,
                    'discount_price' => $product->discount_price,
                    'effective_price' => $effectivePrice,
                    'quantity' => $quantity,
                    'line_total' => $lineTotal,
                ];
            }

            $discountAmount = 0;
            $taxAmount = 0;
            $total = $subtotal - $discountAmount + $taxAmount;

            // Simulasi pembayaran (bulat ke atas ke ribuan)
            $paymentAmount = ceil($total / 1000) * 1000;
            $changeAmount = $paymentAmount - $total;

            $sale = Sale::create([
                'store_id' => $store->id,
                'cashier_id' => $cashier?->id,
                'cashier_name' => $cashier?->name ?? 'Guest',
                'invoice_number' => $this->generateInvoiceNumber($saleDate, $i),
                'sale_date' => $saleDate,
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'tax_amount' => $taxAmount,
                'total' => $total,
                'payment_amount' => $paymentAmount,
                'change_amount' => $changeAmount,
                'status' => 'completed',
            ]);

            foreach ($items as $item) {
                $sale->items()->create($item);
            }
        }

        $this->command->info('30 sales berhasil dibuat.');
    }

    public function generateInvoiceNumber(): string
    {
        $date = now()->format('Ymd');
        $last = Sale::where('invoice_number', 'like', "INV-{$date}-%")
            ->orderByDesc('id')
            ->lockForUpdate()
            ->first();

        $sequence = $last
            ? ((int) substr($last->invoice_number, -4)) + 1
            : 1;

        return 'INV-' . $date . '-' . str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }
}
