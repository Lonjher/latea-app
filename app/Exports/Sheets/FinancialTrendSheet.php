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

class FinancialTrendSheet implements FromArray, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
{
    public function __construct(
        protected array $chartData,
        protected string $chartPeriod,
    ) {}

    public function title(): string
    {
        return 'Tren Penjualan';
    }

    public function headings(): array
    {
        return [
            'Periode',
            'Open (Rp)',
            'High (Rp)',
            'Low (Rp)',
            'Close (Rp)',
            'Total Revenue (Rp)',
            'Jumlah Transaksi',
        ];
    }

    public function array(): array
    {
        $rows = [];
        foreach ($this->chartData as $d) {
            $rows[] = [
                $d['label'],
                $d['o'],
                $d['h'],
                $d['l'],
                $d['c'],
                $d['revenue'],
                $d['count'],
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
