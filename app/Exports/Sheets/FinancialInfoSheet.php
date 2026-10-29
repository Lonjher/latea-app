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

class FinancialInfoSheet implements FromArray, WithTitle, WithStyles, WithColumnWidths, WithEvents, WithCustomStartCell
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

    public function startCell(): string
    {
        return 'A7'; // Tabel dimulai dari baris 7
    }

    public function columnWidths(): array
    {
        return ['A' => 20, 'B' => 70];
    }

    public function array(): array
    {
        return [
            ['Informasi', 'Detail'],
            ['Dibuat pada', now()->format('d M Y, H:i:s')],
            ['Dibuat oleh', auth()->user()?->name ?? 'System'],
            ['', ''],
            ['CATATAN', ''],
            ['HPP', 'Dihitung dari products.initial_price × sale_items.quantity saat transaksi'],
            ['Status', 'Hanya transaksi dengan status = completed yang dihitung'],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            // Header Tabel (Baris 7)
            7 => [
                'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '84A98C']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ],
            // Sub-header CATATAN (Baris 11)
            11 => [
                'font' => ['bold' => true, 'color' => ['rgb' => '1E293B']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F5F5F4']],
            ],
            'A' => ['font' => ['bold' => true]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = 13; // Total baris data (7 sampai 13)

                // Force Font Book Antiqua 10 ke seluruh sheet
                $sheet->getStyle('A1:B' . $lastRow)->getFont()->setName('Book Antiqua')->setSize(10);
                $periode = \Carbon\Carbon::parse($this->dateFrom)->translatedFormat('j M Y') . ' s/d ' . \Carbon\Carbon::parse($this->dateTo)->translatedFormat('j M Y');
                // ─── Header Info ───
                $sheet->setCellValue('A1', 'Informasi Laporan Keuangan');
                $sheet->setCellValue('A2', 'Periode: ' . $periode);
                $sheet->setCellValue('A3', 'Toko: ' . ($this->storeName ?? 'Semua Toko'));

                $sheet->mergeCells('A1:B1');
                $sheet->mergeCells('A2:B2');
                $sheet->mergeCells('A3:B3');

                $sheet->setCellValue('A5', 'INFORMASI & CATATAN');
                $sheet->mergeCells('A5:B5');

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
                $sheet->getStyle('A7:B' . $lastRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E2E8F0']],
                    ],
                ]);

                // Text Wrapping untuk kolom detail agar rapi
                $sheet->getStyle('B7:B' . $lastRow)->getAlignment()->setWrapText(true);
                $sheet->getStyle('A7:B' . $lastRow)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

                // Set Row Height untuk header dan catatan
                $sheet->getRowDimension(1)->setRowHeight(22);
                $sheet->getRowDimension(7)->setRowHeight(22);
                $sheet->getRowDimension(11)->setRowHeight(22);
            },
        ];
    }
}
