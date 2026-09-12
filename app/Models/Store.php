<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $code
 * @property string $location
 * @property boolean $is_active
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */

#[Guarded(['id'])]
class Store extends Model
{
    public function user()
    {
        return $this->hasOne(User::class);
    }
}
