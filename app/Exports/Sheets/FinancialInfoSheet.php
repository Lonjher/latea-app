<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class FinancialInfoSheet implements FromArray, WithTitle, WithStyles, WithColumnWidths
{
    public function __construct(
        protected string $dateFrom,
        protected string $dateTo,
        protected ?string $storeName,
    ) {}

    public function title(): string
    {
        return 'Info Laporan';
    }

    public function columnWidths(): array
    {
        return ['A' => 25, 'B' => 40];
    }

    public function array(): array
    {
        return [
            ['LAPORAN ANALISIS KEUANGAN', ''],
            ['', ''],
            ['Periode', $this->dateFrom . ' s/d ' . $this->dateTo],
            ['Store', $this->storeName ?? 'Semua Store'],
            ['Dibuat pada', now()->format('d M Y, H:i:s')],
            ['Dibuat oleh', auth()->user()?->name ?? 'System'],
            ['', ''],
            ['CATATAN:', ''],
            ['Biaya operasional', 'Diestimasi 10% dari revenue (belum termasuk tabel biaya operasional)'],
            ['HPP', 'Dihitung dari products.initial_price × sale_items.quantity saat transaksi'],
            ['Status', 'Hanya transaksi dengan status = completed yang dihitung'],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '84A98C']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            8 => ['font' => ['bold' => true]],
            'A' => ['font' => ['bold' => true]],
        ];
    }
}
