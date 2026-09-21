<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class FinancialTopProductsSheet implements FromArray, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
{
    public function __construct(protected $topProducts) {}

    public function title(): string
    {
        return 'Top Produk';
    }

    public function headings(): array
    {
        return [
            'Produk',
            'Total Qty',
            'Revenue (Rp)',
            'HPP (Rp)',
            'Laba Kotor (Rp)',
            'Margin (%)',
        ];
    }

    public function array(): array
    {
        $rows = [];
        foreach ($this->topProducts as $p) {
            $rows[] = [
                $p->product_name,
                (int) $p->total_qty,
                (float) $p->total_revenue,
                (float) $p->total_cogs,
                (float) $p->gross_profit,
                round((float) $p->margin_pct, 2),
            ];
        }
        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '84A98C']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }
}
