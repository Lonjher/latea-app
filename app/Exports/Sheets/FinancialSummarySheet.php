<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class FinancialSummarySheet implements FromArray, WithTitle, WithStyles, WithColumnWidths, ShouldAutoSize
{
    public function __construct(
        protected array $metrics,
        protected string $dateFrom,
        protected string $dateTo,
        protected ?string $storeName,
    ) {}

    public function title(): string
    {
        return 'Ringkasan';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 35,
            'B' => 25,
        ];
    }

    public function array(): array
    {
        $m = $this->metrics;

        return [
            ['LAPORAN ANALISIS KEUANGAN', ''],
            ['', ''],
            ['Periode', $this->dateFrom . ' s/d ' . $this->dateTo],
            ['Store', $this->storeName ?? 'Semua Store'],
            ['Dibuat', now()->format('d M Y H:i')],
            ['', ''],
            ['A. PENDAPATAN', ''],
            ['Total Penjualan (Revenue)', 'Rp ' . number_format($m['totalRevenue'], 0, ',', '.')],
            ['Jumlah Transaksi', $m['transactionCount']],
            ['Total Item Terjual', $m['totalItemsSold']],
            ['', ''],
            ['B. HARGA POKOK PENJUALAN (HPP)', ''],
            ['Total HPP (COGS)', 'Rp ' . number_format($m['totalCogs'], 0, ',', '.')],
            ['HPP % dari Revenue', $m['totalRevenue'] > 0
                ? number_format(($m['totalCogs'] / $m['totalRevenue']) * 100, 2) . '%'
                : '0%'],
            ['', ''],
            ['C. LABA KOTOR (GROSS PROFIT)', ''],
            ['Laba Kotor', 'Rp ' . number_format($m['grossProfit'], 0, ',', '.')],
            ['Gross Margin', number_format($m['grossMarginPct'], 2) . '%'],
            ['', ''],
            ['D. BIAYA OPERASIONAL (ESTIMASI)', ''],
            ['Biaya Operasional', 'Rp ' . number_format($m['operationalCost'], 0, ',', '.')],
            ['', ''],
            ['E. LABA BERSIH (NET PROFIT)', ''],
            ['Laba Bersih', 'Rp ' . number_format($m['netProfit'], 0, ',', '.')],
            ['Net Margin', number_format($m['netMarginPct'], 2) . '%'],
            ['', ''],
            ['F. PRICING METRICS', ''],
            ['Markup', number_format($m['markupPct'], 2) . '%'],
            ['Harga Rata-rata per Item', 'Rp ' . number_format($m['avgSellingPrice'], 0, ',', '.')],
            ['HPP Rata-rata per Item', 'Rp ' . number_format($m['avgCogsPerItem'], 0, ',', '.')],
            ['Contribution Margin per Item', 'Rp ' . number_format($m['contributionMarginPerUnit'], 0, ',', '.')],
            ['', ''],
            ['G. BREAK-EVEN ANALYSIS', ''],
            ['Break-Even Point (unit)', number_format($m['bepUnits'], 0, ',', '.') . ' unit'],
            ['Break-Even Revenue', 'Rp ' . number_format($m['bepRevenue'], 0, ',', '.')],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '84A98C']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ],
            7  => ['font' => ['bold' => true, 'size' => 11], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E7E5E4']]],
            12 => ['font' => ['bold' => true, 'size' => 11], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E7E5E4']]],
            16 => ['font' => ['bold' => true, 'size' => 11], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E7E5E4']]],
            20 => ['font' => ['bold' => true, 'size' => 11], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E7E5E4']]],
            23 => ['font' => ['bold' => true, 'size' => 11], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E7E5E4']]],
            28 => ['font' => ['bold' => true, 'size' => 11], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E7E5E4']]],
            34 => ['font' => ['bold' => true, 'size' => 11], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E7E5E4']]],
            'A' => ['font' => ['bold' => true]],
        ];
    }
}
