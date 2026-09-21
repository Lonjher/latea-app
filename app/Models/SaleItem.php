<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Guarded(['id'])]
class SaleItem extends Model
{
    protected $casts = [
        'price' => 'decimal:2',
        'discount_price' => 'decimal:2',
        'line_total' => 'decimal:2',
        'quantity' => 'integer',
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Harga efektif berdasarkan qty (untuk perhitungan, bukan snapshot).
     */
    public function effectivePrice(): float
    {
        if ($this->discount_price && $this->quantity >= ($this->product->minimal_discount ?? 1)) {
            return (float) $this->discount_price;
        }

        return (float) $this->price;
    }
}
