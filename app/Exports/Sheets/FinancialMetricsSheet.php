<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class FinancialMetricsSheet implements FromArray, WithTitle, WithStyles, WithColumnWidths, WithEvents, WithCustomStartCell
{
    public function __construct(
        protected array $metrics,
        protected string $dateFrom,
        protected string $dateTo,
        protected ?string $storeName,
    ) {}

    public function title(): string
    {
        return 'Metrik Lengkap';
    }

    public function startCell(): string
    {
        return 'A7'; // Tabel utama mulai dari baris 7
    }

    public function columnWidths(): array
    {
        return ['A' => 35, 'B' => 20, 'C' => 60];
    }

    public function array(): array
    {
        $m = $this->metrics;
        return [
            ['Metrik', 'Nilai', 'Formula / Deskripsi'], // Baris 7
            ['PENDAPATAN', '', ''], // Baris 8
            ['Total Revenue', $m['totalRevenue'], 'SUM(sales.total) WHERE status = completed'],
            ['Jumlah Transaksi', $m['transactionCount'], 'COUNT(sales)'],
            ['Total Item Terjual', $m['totalItemsSold'], 'SUM(sale_items.quantity)'],
            ['', '', ''],
            ['COGS', '', ''],
            ['Total HPP', $m['totalCogs'], 'SUM(products.initial_price × sale_items.quantity)'],
            ['', '', ''],
            ['PROFITABILITAS', '', ''],
            ['Laba Kotor', $m['grossProfit'], 'Revenue − HPP'],
            ['Gross Margin %', $m['grossMarginPct'] / 100, '(Penjualan − HPP) / Penjualan × 100%'],
            ['Biaya Operasional', $m['operationalCost'], '10% dari Revenue (estimasi)'],
            ['Laba Bersih', $m['netProfit'], 'Laba Kotor − Biaya Operasional'],
            ['Net Margin %', $m['netMarginPct'] / 100, '(Penjualan − Total Biaya) / Penjualan × 100%'],
            ['', '', ''],
            ['PRICING', '', ''],
            ['Markup %', $m['markupPct'] / 100, '(Harga Jual − HPP) / HPP × 100%'],
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
            // Header Tabel di Baris 7
            7 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '84A98C']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ],
            // Sub-Header Seksi (PENDAPATAN, COGS, dll)
            8  => ['font' => ['bold' => true, 'color' => ['rgb' => '1E293B']], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F5F5F4']]],
            13 => ['font' => ['bold' => true, 'color' => ['rgb' => '1E293B']], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F5F5F4']]],
            16 => ['font' => ['bold' => true, 'color' => ['rgb' => '1E293B']], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F5F5F4']]],
            23 => ['font' => ['bold' => true, 'color' => ['rgb' => '1E293B']], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F5F5F4']]],
            29 => ['font' => ['bold' => true, 'color' => ['rgb' => '1E293B']], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F5F5F4']]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = 31;

                // Force Font Book Antiqua 10 ke seluruh sheet
                $sheet->getStyle('A1:C' . $lastRow)->getFont()->setName('Book Antiqua')->setSize(10);

                // Border untuk Tabel
                $sheet->getStyle('A7:C' . $lastRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E2E8F0']],
                    ],
                ]);

                // Format Currencies (Rp)
                foreach (['B9', 'B14', 'B17', 'B19', 'B20', 'B25', 'B26', 'B27', 'B31'] as $cell) {
                    $sheet->getStyle($cell)->getNumberFormat()->setFormatCode('"Rp" #,##0');
                }

                // Format Percentages (%)
                foreach (['B18', 'B21', 'B24'] as $cell) {
                    $sheet->getStyle($cell)->getNumberFormat()->setFormatCode('0.00%');
                }

                // Format Numbers
                foreach (['B10', 'B11', 'B30'] as $cell) {
                    $sheet->getStyle($cell)->getNumberFormat()->setFormatCode('#,##0');
                }
                $periode = \Carbon\Carbon::parse($this->dateFrom)->translatedFormat('j M Y') . ' s/d ' . \Carbon\Carbon::parse($this->dateTo)->translatedFormat('j M Y');

                // ─── Header Info ───
                $sheet->setCellValue('A1', 'Metrik & Formula Keuangan');
                $sheet->setCellValue('A2', 'Periode: ' . $periode);
                $sheet->setCellValue('A3', 'Toko: ' . ($this->storeName ?? 'Semua Toko'));

                $sheet->mergeCells('A1:C1');
                $sheet->mergeCells('A2:C2');
                $sheet->mergeCells('A3:C3');

                $sheet->setCellValue('A5', 'RINGKASAN KEUANGAN');
                $sheet->mergeCells('A5:C5');

                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '1E293B']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
                ]);

                $sheet->getStyle('A2:A3')->applyFromArray([
                    'font' => ['size' => 10, 'color' => ['rgb' => '64748B']],
                ]);

                $sheet->getStyle('A5')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => '1E293B']],
                ]);
            },
        ];
    }
}
