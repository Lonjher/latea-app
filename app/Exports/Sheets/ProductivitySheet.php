<?php

namespace App\Exports\Sheets;

use Illuminate\Support\Collection;
use Illuminate\Support\Enumerable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductivitySheet implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithTitle,
    WithStyles,
    WithColumnWidths,
    ShouldAutoSize,
    WithCustomStartCell,
    WithEvents
{
    protected Collection $products;
    protected int $dataStartRow;

    public function __construct(
        $products,
        protected array $summary,
        protected string $dateFrom,
        protected string $dateTo,
        protected ?string $storeName = null,
    ) {
        $this->products = $products instanceof Collection ? $products : collect($products ?? []);
        $this->dataStartRow = 9; // Data mulai di baris 9
    }

    public function startCell(): string
    {
        return 'A8'; // Header tabel di baris 8
    }

    public function title(): string
    {
        return 'Analisis Produktivitas';
    }

    public function collection(): Enumerable
    {
        return $this->products;
    }

    public function headings(): array
    {
        return [
            'No', 'Kode Produk', 'Nama Produk', 'Status', 'Qty Terjual', 'Transaksi',
            'Hari Aktif', 'Konsistensi %', 'Velocity (unit/hari)', 'Revenue/Hari', 'Tren', 'Growth %',
        ];
    }

    public function map($product): array
    {
        static $no = 0;
        $no++;

        return [
            $no, $product->code, $product->name, $this->statusLabel($product->status),
            (int) $product->total_qty, (int) $product->transaction_count, (int) $product->days_active,
            round($product->consistency_pct / 100, 4), round($product->velocity, 2), (float) $product->revenue_per_day,
            $this->trendLabel($product->trend), round($product->growth_pct / 100, 4),
        ];
    }

    protected function statusLabel(string $status): string
    {
        return match ($status) {
            'fast' => 'Cepat Laku',
            'normal' => 'Normal',
            'slow' => 'Lambat',
            'dead' => 'Dead Stock',
            default => '-',
        };
    }

    protected function trendLabel(string $trend): string
    {
        return match ($trend) {
            'growing' => 'Naik',
            'declining' => 'Turun',
            default => 'Stabil',
        };
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5, 'B' => 14, 'C' => 28, 'D' => 14, 'E' => 10, 'F' => 10,
            'G' => 10, 'H' => 14, 'I' => 16, 'J' => 14, 'K' => 10, 'L' => 10,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            // Style untuk Header Tabel (Baris 8)
            8 => [
                'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '84A98C']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = $this->dataStartRow + $this->products->count() - 1;

                // Force Font Book Antiqua 10 ke seluruh area sheet
                $sheet->getStyle('A1:L' . max($lastRow, 8))->getFont()->setName('Book Antiqua')->setSize(10);

                // ─── Header Info ───
                $sheet->setCellValue('A1', 'Analisis Produktivitas Produk');
                $periode = \Carbon\Carbon::parse($this->dateFrom)->translatedFormat('j M Y') . ' s/d ' . \Carbon\Carbon::parse($this->dateTo)->translatedFormat('j M Y');
                $sheet->setCellValue('A2', 'Periode: ' . $periode);
                $sheet->setCellValue('A3', 'Toko: ' . ($this->storeName ?? 'Semua Toko'));

                $sheet->mergeCells('A1:L1');
                $sheet->mergeCells('A2:L2');
                $sheet->mergeCells('A3:L3');

                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '1E293B']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
                ]);

                $sheet->getStyle('A2:A3')->applyFromArray([
                    'font' => ['size' => 10, 'color' => ['rgb' => '64748B']],
                ]);

                // ─── Ringkasan KPI ───
                $summary = $this->summary;
                $sheet->setCellValue('A5', 'RINGKASAN');
                $sheet->mergeCells('A5:L5');
                $sheet->getStyle('A5')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => '1E293B']],
                ]);

                $kpiRow1 = 6;
                $kpiRow2 = 7;

                $kpis = [
                    ['Total Produk', $summary['total_products'] ?? 0],
                    ['Cepat Laku', $summary['fast_count'] ?? 0],
                    ['Normal', $summary['normal_count'] ?? 0],
                    ['Lambat', $summary['slow_count'] ?? 0],
                    ['Dead Stock', $summary['dead_count'] ?? 0],
                    ['Tren Naik', $summary['growing_count'] ?? 0],
                    ['Tren Turun', $summary['declining_count'] ?? 0],
                    ['Total Hari', $summary['total_days'] ?? 0],
                    ['Ø Velocity', round($summary['avg_velocity'] ?? 0, 2)],
                ];

                foreach ($kpis as $i => $kpi) {
                    $col = chr(65 + $i);
                    $sheet->setCellValue($col . $kpiRow1, $kpi[0]);
                    $sheet->setCellValue($col . $kpiRow2, $kpi[1]);

                    $sheet->getStyle($col . $kpiRow1)->applyFromArray([
                        'font' => ['size' => 9, 'color' => ['rgb' => '64748B']],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F5F5F4']],
                    ]);
                    $sheet->getStyle($col . $kpiRow2)->applyFromArray([
                        'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => '1E293B']],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F5F5F4']],
                    ]);
                }

                // ─── Format Data Tabel ───
                if ($lastRow >= $this->dataStartRow) {
                    $range = 'A' . $this->dataStartRow . ':L' . $lastRow;

                    // Borders
                    $sheet->getStyle($range)->applyFromArray([
                        'borders' => [
                            'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E2E8F0']],
                        ],
                    ]);

                    $sheet->getStyle($range)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

                    // Alignment Tengah
                    foreach (['A', 'D', 'G', 'H', 'I', 'K', 'L'] as $col) {
                        $sheet->getStyle($col . $this->dataStartRow . ':' . $col . $lastRow)
                            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    }

                    // Alignment Kanan
                    foreach (['E', 'F', 'J'] as $col) {
                        $sheet->getStyle($col . $this->dataStartRow . ':' . $col . $lastRow)
                            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    }

                    // Format Angka
                    $sheet->getStyle('E' . $this->dataStartRow . ':G' . $lastRow)
                        ->getNumberFormat()->setFormatCode('#,##0');
                    $sheet->getStyle('I' . $this->dataStartRow . ':I' . $lastRow)
                        ->getNumberFormat()->setFormatCode('#,##0.00');
                    $sheet->getStyle('H' . $this->dataStartRow . ':H' . $lastRow)
                        ->getNumberFormat()->setFormatCode('0.00%');
                    $sheet->getStyle('J' . $this->dataStartRow . ':J' . $lastRow)
                        ->getNumberFormat()->setFormatCode('"Rp" #,##0');
                    $sheet->getStyle('L' . $this->dataStartRow . ':L' . $lastRow)
                        ->getNumberFormat()->setFormatCode('0.00%');

                    // Zebra stripes
                    for ($row = $this->dataStartRow; $row <= $lastRow; $row++) {
                        if (($row - $this->dataStartRow) % 2 === 1) {
                            $sheet->getStyle('A' . $row . ':L' . $row)->applyFromArray([
                                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F8FAFC']],
                            ]);
                        }
                    }

                    // Conditional coloring
                    $row = $this->dataStartRow;
                    foreach ($this->products as $product) {
                        $statusColor = match ($product->status) {
                            'fast' => 'DCFCE7', 'normal' => 'DBEAFE', 'slow' => 'FEF3C7', 'dead' => 'FEE2E2', default => 'F1F5F9',
                        };
                        $statusFontColor = match ($product->status) {
                            'fast' => '15803D', 'normal' => '1D4ED8', 'slow' => 'B45309', 'dead' => 'B91C1C', default => '64748B',
                        };
                        $sheet->getStyle('D' . $row)->applyFromArray([
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $statusColor]],
                            'font' => ['bold' => true, 'color' => ['rgb' => $statusFontColor], 'size' => 9],
                        ]);

                        $trendFontColor = match ($product->trend) {
                            'growing' => '15803D', 'declining' => 'B91C1C', default => '64748B',
                        };
                        $sheet->getStyle('K' . $row)->applyFromArray([
                            'font' => ['bold' => true, 'color' => ['rgb' => $trendFontColor], 'size' => 9],
                        ]);

                        $row++;
                    }
                }

                // Freeze & Filter
                $sheet->freezePane('A' . $this->dataStartRow);
                if ($lastRow >= $this->dataStartRow) {
                    $sheet->setAutoFilter('A8:L' . $lastRow);
                }

                $sheet->getRowDimension(1)->setRowHeight(22);
                $sheet->getRowDimension(8)->setRowHeight(22);
            },
        ];
    }
}
