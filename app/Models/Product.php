<?php

namespace App\Models;

use App\Casts\RupiahCast;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string $description
 * @property string $image
 * @property float $initial_price
 * @property float $discount_price
 * @property float $minimal_discount
 * @property float $price
 * @property boolean $is_active
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
#[Guarded(['id'])]
class Product extends Model
{
    protected function casts(): array
    {
        return [
            'initial_price' => RupiahCast::class,
            'discount_price' => RupiahCast::class,
            'price' => RupiahCast::class,
        ];
    }
}
