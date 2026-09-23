<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class FinancialMetricsSheet implements FromArray, WithTitle, WithStyles, WithColumnWidths
{
    public function __construct(protected array $metrics) {}

    public function title(): string
    {
        return 'Metrik Lengkap';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 35,
            'B' => 20,
            'C' => 60,
        ];
    }

    public function array(): array
    {
        $m = $this->metrics;

        return [
            ['Metrik', 'Nilai', 'Formula / Deskripsi'],
            ['PENDAPATAN', '', ''],
            ['Total Revenue', $m['totalRevenue'], 'SUM(sales.total) WHERE status = completed'],
            ['Jumlah Transaksi', $m['transactionCount'], 'COUNT(sales)'],
            ['Total Item Terjual', $m['totalItemsSold'], 'SUM(sale_items.quantity)'],
            ['', '', ''],
            ['COGS', '', ''],
            ['Total HPP', $m['totalCogs'], 'SUM(products.initial_price × sale_items.quantity)'],
            ['', '', ''],
            ['PROFITABILITAS', '', ''],
            ['Laba Kotor', $m['grossProfit'], 'Revenue − HPP'],
            ['Gross Margin %', round($m['grossMarginPct'], 2), '(Penjualan − HPP) / Penjualan × 100%'],
            ['Biaya Operasional', $m['operationalCost'], '10% dari Revenue (estimasi)'],
            ['Laba Bersih', $m['netProfit'], 'Laba Kotor − Biaya Operasional'],
            ['Net Margin %', round($m['netMarginPct'], 2), '(Penjualan − Total Biaya) / Penjualan × 100%'],
            ['', '', ''],
            ['PRICING', '', ''],
            ['Markup %', round($m['markupPct'], 2), '(Harga Jual − HPP) / HPP × 100%'],
            ['Harga Rata-rata', $m['avgSellingPrice'], 'Revenue / Total Item'],
            ['HPP Rata-rata', $m['avgCogsPerItem'], 'Total HPP / Total Item'],
            ['Contribution Margin/Item', $m['contributionMarginPerUnit'], 'Harga Rata-rata − HPP Rata-rata'],
            ['', '', ''],
            ['BREAK-EVEN', '', ''],
            ['BEP Unit', $m['bepUnits'], 'Biaya Operasional / Contribution Margin per Item'],
            ['BEP Revenue', $m['bepRevenue'], 'BEP Unit × Harga Rata-rata'],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '84A98C']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            2  => ['font' => ['bold' => true], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F5F5F4']]],
            7  => ['font' => ['bold' => true], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F5F5F4']]],
            10 => ['font' => ['bold' => true], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F5F5F4']]],
            17 => ['font' => ['bold' => true], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F5F5F4']]],
            23 => ['font' => ['bold' => true], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F5F5F4']]],
        ];
    }
}
