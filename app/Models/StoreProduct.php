<?php

namespace App\Models;

use App\Casts\RupiahCast;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Relations\Pivot;

#[Guarded(['id'])]
class StoreProduct extends Pivot
{
    protected $table = 'store_products';

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
