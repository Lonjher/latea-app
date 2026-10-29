<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class FinancialSummarySheet implements FromArray, WithTitle, WithStyles, WithColumnWidths, ShouldAutoSize, WithEvents
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
        $periode = \Carbon\Carbon::parse($this->dateFrom)->translatedFormat('j M Y') . ' s/d ' . \Carbon\Carbon::parse($this->dateTo)->translatedFormat('j M Y');
        return [
            ['LAPORAN ANALISIS KEUANGAN', ''],
            ['', ''],
            ['Periode', $periode],
            ['Store', $this->storeName ?? 'Semua Store'],
            ['Dibuat', now()->format('d M Y H:i')],
            ['', ''],
            ['A. PENDAPATAN', ''],
            ['Total Penjualan (Revenue)', $m['totalRevenue']],
            ['Jumlah Transaksi', $m['transactionCount']],
            ['Total Item Terjual', $m['totalItemsSold']],
            ['', ''],
            ['B. HARGA POKOK PENJUALAN (HPP)', ''],
            ['Total HPP (COGS)', $m['totalCogs']],
            ['HPP % dari Revenue', $m['totalRevenue'] > 0 ? $m['totalCogs'] / $m['totalRevenue'] : 0],
            ['', ''],
            ['C. LABA KOTOR (GROSS PROFIT)', ''],
            ['Laba Kotor', $m['grossProfit']],
            ['Gross Margin', $m['grossMarginPct'] / 100],
            ['', ''],
            ['D. BIAYA OPERASIONAL (ESTIMASI)', ''],
            ['Biaya Operasional', $m['operationalCost']],
            ['', ''],
            ['E. LABA BERSIH (NET PROFIT)', ''],
            ['Laba Bersih', $m['netProfit']],
            ['Net Margin', $m['netMarginPct'] / 100],
            ['', ''],
            ['F. PRICING METRICS', ''],
            ['Markup', $m['markupPct'] / 100],
            ['Harga Rata-rata per Item', $m['avgSellingPrice']],
            ['HPP Rata-rata per Item', $m['avgCogsPerItem']],
            ['Contribution Margin per Item', $m['contributionMarginPerUnit']],
            ['', ''],
            ['G. BREAK-EVEN ANALYSIS', ''],
            ['Break-Even Point (unit)', $m['bepUnits']],
            ['Break-Even Revenue', $m['bepRevenue']],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '84A98C']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ],
            7  => ['font' => ['bold' => true, 'color' => ['rgb' => '1E293B']], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F5F5F4']]],
            12 => ['font' => ['bold' => true, 'color' => ['rgb' => '1E293B']], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F5F5F4']]],
            16 => ['font' => ['bold' => true, 'color' => ['rgb' => '1E293B']], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F5F5F4']]],
            20 => ['font' => ['bold' => true, 'color' => ['rgb' => '1E293B']], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F5F5F4']]],
            23 => ['font' => ['bold' => true, 'color' => ['rgb' => '1E293B']], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F5F5F4']]],
            28 => ['font' => ['bold' => true, 'color' => ['rgb' => '1E293B']], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F5F5F4']]],
            34 => ['font' => ['bold' => true, 'color' => ['rgb' => '1E293B']], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F5F5F4']]],
            'A' => ['font' => ['bold' => true]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Force Font Book Antiqua 10 ke seluruh area tabel
                $sheet->getStyle('A1:B35')->getFont()->setName('Book Antiqua')->setSize(10);

                // Borders
                $sheet->getStyle('A1:B35')->applyFromArray([
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E2E8F0']],
                    ],
                ]);

                // Format Currencies (Rp)
                foreach (['B8', 'B13', 'B17', 'B21', 'B24', 'B29', 'B30', 'B31', 'B35'] as $cell) {
                    $sheet->getStyle($cell)->getNumberFormat()->setFormatCode('"Rp" #,##0');
                }

                // Format Percentages (%)
                foreach (['B14', 'B18', 'B25', 'B28'] as $cell) {
                    $sheet->getStyle($cell)->getNumberFormat()->setFormatCode('0.00%');
                }

                // Format Numbers
                foreach (['B9', 'B10', 'B34'] as $cell) {
                    $sheet->getStyle($cell)->getNumberFormat()->setFormatCode('#,##0');
                }
            },
        ];
    }
}
