<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Model;

#[Guarded(['id'])]
class SaleItem extends Model
{
    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }
}
