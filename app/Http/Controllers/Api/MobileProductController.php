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

        if (! $storeId) {
            return response()->json([
                'message' => 'Kasir belum ditugaskan ke toko manapun.',
            ], 403);
        }

        $products = Product::query()
            ->where('is_active', true)
            ->whereHas('stores', function ($q) use ($storeId) {
                $q->where('stores.id', $storeId)
                  ->where('store_products.is_available', true);
            })
            ->with([
                'stores' => function ($q) use ($storeId) {
                    $q->where('stores.id', $storeId)
                      ->withPivot(['price', 'is_available']);
                },
            ])
            ->orderBy('name')
            ->get()
            ->map(function (Product $product) use ($storeId) {
                $pivot = $product->stores->first()?->pivot;

                // Harga khusus toko (jika di-set), fallback ke harga produk
                $storePrice = $pivot?->price !== null
                    ? (float) $pivot->price
                    : (float) $product->price;

                return [
                    'id'                => $product->id,
                    'code'              => $product->code,
                    'name'              => $product->name,
                    'emoji'             => null, // sementara — bisa diisi nanti
                    'image_url'         => $product->image
                        ? Storage::disk('public')->url($product->image)
                        : null,
                    'price'             => $storePrice,
                    'discount_price'    => $product->discount_price !== null
                        ? (float) $product->discount_price
                        : null,
                    'minimal_discount'  => $product->minimal_discount,
                    'is_active'         => (bool) $product->is_active,
                ];
            });

        return response()->json([
            'data'  => $products,
            'meta'  => [
                'store_id'    => $storeId,
                'total'       => $products->count(),
            ],
        ]);
    }
}
