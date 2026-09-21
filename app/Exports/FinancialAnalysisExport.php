<?php

namespace App\Exports;

use App\Exports\Sheets\FinancialInfoSheet;
use App\Exports\Sheets\FinancialMetricsSheet;
use App\Exports\Sheets\FinancialSummarySheet;
use App\Exports\Sheets\FinancialTopProductsSheet;
use App\Exports\Sheets\FinancialTrendSheet;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\Exportable; // Import trait bawaan package

class FinancialAnalysisExport implements WithMultipleSheets, FromArray
{
    use Exportable;

    public function array(): array
    {
        return [];
    }

    public function __construct(
        protected array $metrics,
        protected array $chartData,
        protected $topProducts,
        protected string $dateFrom,
        protected string $dateTo,
        protected ?string $storeName,
        protected string $chartPeriod,
    ) {}

    public function sheets(): array
    {
        return [
            new FinancialSummarySheet($this->metrics, $this->dateFrom, $this->dateTo, $this->storeName),
            new FinancialTrendSheet($this->chartData, $this->chartPeriod),
            new FinancialTopProductsSheet($this->topProducts),
            new FinancialMetricsSheet($this->metrics),
            new FinancialInfoSheet($this->dateFrom, $this->dateTo, $this->storeName),
        ];
    }
}
