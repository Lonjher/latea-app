<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Number;

class RupiahCast implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if (is_null($value)) return null;

        // Menghasilkan format: Rp 1.500.000,00
        return Number::currency($value, in: env('APP_CURRENCY', 'IDR'), locale: env('APP_LOCALE', 'id'));
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if (is_null($value)) return null;

        // Membersihkan string Rupiah agar kembali menjadi angka integer/float murni
        if (is_string($value)) {
            return (int) preg_replace('/[^0-9]/', '', $value);
        }

        return $value;
    }
}
