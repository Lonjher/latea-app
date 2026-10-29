<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MobileProductController extends Controller
{
    /**
     * List produk yang tersedia di store kasir yang sedang login.
     * Hanya produk dengan is_available = true di pivot store_product.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $storeId = $user->store_id;

        if (!$storeId) {
            return response()->json([
                'message' => 'Kasir belum ditugaskan ke toko manapun.',
            ], 403);
        }

        $products = Product::query()
            ->where('is_active', true)
            ->whereHas('stores', function ($q) use ($storeId) {
                // Hanya ambil produk yang di-assign ke toko ini
                $q->where('stores.id', $storeId);
            })
            ->with([
                'stores' => function ($q) use ($storeId) {
                    $q->where('stores.id', $storeId)
                    ->withPivot(['price', 'is_available']);
                },
            ])
            // ⭐ Urutkan berdasarkan abjad (code) untuk tampilan default
            ->orderBy('code', 'asc')
            ->get()
            ->map(function (Product $product) use ($storeId) {
                $pivot = $product->stores->first()?->pivot;

                // Harga khusus toko (jika di-set), fallback ke harga produk
                $storePrice = $pivot?->price !== null
                    ? (float) $pivot->price
                    : (float) $product->price;

                return [
                    'id' => $product->id,
                    'code' => $product->code,
                    'name' => $product->name,
                    'emoji' => null,
                    'image_url' => $product->image
                        ? asset('storage/' . $product->image)
                        : null,
                    'price' => $storePrice,
                    'discount_price' => $product->discount_price !== null
                        ? (float) $product->discount_price
                        : null,
                    'minimal_discount' => $product->minimal_discount,
                    'is_active' => (bool) $product->is_active,
                    'is_available' => (bool) ($pivot?->is_available ?? false),
                ];
            });

        return response()->json([
            'data' => $products,
            'meta' => [
                'store_id' => $storeId,
                'total' => $products->count(),
            ],
        ]);
    }
}
