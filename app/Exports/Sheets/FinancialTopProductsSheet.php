<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class FinancialTopProductsSheet implements FromArray, WithHeadings, WithTitle, WithStyles, ShouldAutoSize, WithEvents, WithCustomStartCell
{
    public function __construct(
        protected $topProducts,
        protected string $dateFrom,
        protected string $dateTo,
        protected ?string $storeName,
    ) {}

    public function title(): string
    {
        return 'Top Produk';
    }

    public function startCell(): string
    {
        return 'A7'; // Header tabel mulai di baris 7
    }

    public function headings(): array
    {
        return [
            'Produk', 'Total Qty', 'Revenue (Rp)', 'HPP (Rp)', 'Laba Kotor (Rp)', 'Margin (%)',
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
                round((float) $p->margin_pct / 100, 4),
            ];
        }
        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            // Style untuk Header Tabel (Baris 7)
            7 => [
                'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '84A98C']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                // Data mulai baris 8, jadi lastRow = 7 + jumlah data
                $lastRow = count($this->topProducts) + 7;

                // Force Font Book Antiqua 10 ke seluruh sheet
                $sheet->getStyle('A1:F' . $lastRow)->getFont()->setName('Book Antiqua')->setSize(10);

                // ─── Header Info ───
                $sheet->setCellValue('A1', 'Analisis Top Produk');
                $periode = \Carbon\Carbon::parse($this->dateFrom)->translatedFormat('j M Y') . ' s/d ' . \Carbon\Carbon::parse($this->dateTo)->translatedFormat('j M Y');
                $sheet->setCellValue('A2', 'Periode: ' . $periode);
                $sheet->setCellValue('A3', 'Toko: ' . ($this->storeName ?? 'Semua Toko'));

                $sheet->mergeCells('A1:F1');
                $sheet->mergeCells('A2:F2');
                $sheet->mergeCells('A3:F3');

                $sheet->setCellValue('A5', 'RINGKASAN TOP PRODUK');
                $sheet->mergeCells('A5:F5');

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

                // ─── Format Tabel ───
                if ($lastRow >= 7) {
                    // Borders
                    $sheet->getStyle('A7:F' . $lastRow)->applyFromArray([
                        'borders' => [
                            'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E2E8F0']],
                        ],
                    ]);

                    // Format Qty (Mulai baris 8)
                    $sheet->getStyle('B8:B' . $lastRow)->getNumberFormat()->setFormatCode('#,##0');

                    // Format Currencies (Revenue, HPP, Laba Kotor)
                    $sheet->getStyle('C8:E' . $lastRow)->getNumberFormat()->setFormatCode('"Rp" #,##0');

                    // Format Percentages (Margin)
                    $sheet->getStyle('F8:F' . $lastRow)->getNumberFormat()->setFormatCode('0.00%');
                }

                $sheet->getRowDimension(1)->setRowHeight(22);
                $sheet->getRowDimension(7)->setRowHeight(22);
            },
        ];
    }
}
