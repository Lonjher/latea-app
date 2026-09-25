<?php

namespace App\Services;

use App\Models\Sale;
use Illuminate\Support\Facades\DB;

class InvoiceNumberService
{
    /**
     * Generate invoice number dengan format: INV-YYYYMMDD-NNNN
     * Menggunakan lockForUpdate agar aman dari race condition
     * saat 2 kasir submit transaksi bersamaan.
     */
    public function generate(): string
    {
        $date   = now()->format('Ymd');
        $prefix = "INV-{$date}-";

        return DB::transaction(function () use ($prefix) {
            $last = Sale::where('invoice_number', 'like', "{$prefix}%")
                ->lockForUpdate()
                ->orderByDesc('invoice_number')
                ->first();

            $nextNumber = $last
                ? ((int) substr($last->invoice_number, -4)) + 1
                : 1;

            return $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
        });
    }
}
