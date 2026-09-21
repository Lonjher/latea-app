<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $code
 * @property string $location
 * @property boolean $is_active
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @method <User> user()
 */

#[Guarded(['id'])]
class Store extends Model
{
    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }
}
