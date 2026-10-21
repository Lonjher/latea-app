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

class FinancialTrendSheet implements FromArray, WithHeadings, WithTitle, WithStyles, ShouldAutoSize, WithEvents, WithCustomStartCell
{
    public function __construct(
        protected array $chartData,
        protected string $chartPeriod,
        protected string $dateFrom,
        protected string $dateTo,
        protected ?string $storeName,
    ) {}

    public function title(): string
    {
        return 'Tren Penjualan';
    }

    public function startCell(): string
    {
        return 'A7'; // Header tabel mulai di baris 7
    }

    public function headings(): array
    {
        return [
            'Periode', 'Open (Rp)', 'High (Rp)', 'Low (Rp)', 'Close (Rp)', 'Total Revenue (Rp)', 'Jumlah Transaksi',
        ];
    }

    public function array(): array
    {
        $rows = [];
        foreach ($this->chartData as $d) {
            $rows[] = [
                $d['label'], $d['o'], $d['h'], $d['l'], $d['c'], $d['revenue'], $d['count'],
            ];
        }
        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            // Style untuk Header Tabel (Baris 7)
            7 => [
                'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']],
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
                $lastRow = count($this->chartData) + 7;

                // Force Font Book Antiqua 10 ke seluruh sheet
                $sheet->getStyle('A1:G' . $lastRow)->getFont()->setName('Book Antiqua')->setSize(10);

                // ─── Header Info ───
                $sheet->setCellValue('A1', 'Analisis Tren Penjualan');
                $periode = \Carbon\Carbon::parse($this->dateFrom)->translatedFormat('j M Y') . ' s/d ' . \Carbon\Carbon::parse($this->dateTo)->translatedFormat('j M Y');
                $sheet->setCellValue('A2', 'Periode: ' . $periode);
                $sheet->setCellValue('A3', 'Toko: ' . ($this->storeName ?? 'Semua Toko'));

                $sheet->mergeCells('A1:G1');
                $sheet->mergeCells('A2:G2');
                $sheet->mergeCells('A3:G3');

                $sheet->setCellValue('A5', 'RINGKASAN TREN');
                $sheet->mergeCells('A5:G5');

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
                    $sheet->getStyle('A7:G' . $lastRow)->applyFromArray([
                        'borders' => [
                            'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E2E8F0']],
                        ],
                    ]);

                    // Format Currencies (Mulai baris 8)
                    $sheet->getStyle('B8:F' . $lastRow)->getNumberFormat()->setFormatCode('"Rp" #,##0');

                    // Format Numbers
                    $sheet->getStyle('G8:G' . $lastRow)->getNumberFormat()->setFormatCode('#,##0');
                }

                $sheet->getRowDimension(1)->setRowHeight(22);
                $sheet->getRowDimension(7)->setRowHeight(22);
            },
        ];
    }
}
