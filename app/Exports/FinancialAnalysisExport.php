<?php

namespace App\Exports;

use App\Exports\Sheets\FinancialInfoSheet;
use App\Exports\Sheets\FinancialMetricsSheet;
use App\Exports\Sheets\FinancialSummarySheet;
use App\Exports\Sheets\FinancialTopProductsSheet;
use App\Exports\Sheets\FinancialTrendSheet;
use App\Exports\Sheets\ProductivitySheet;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\Exportable;

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
        protected $productivityProducts = null,
        protected array $productivitySummary = [],
    ) {}

    public function sheets(): array
    {
        return [
            new FinancialSummarySheet($this->metrics, $this->dateFrom, $this->dateTo, $this->storeName),
            new FinancialTrendSheet(
                chartData: $this->chartData,
                chartPeriod: $this->chartPeriod,
                dateFrom: $this->dateFrom,
                dateTo: $this->dateTo,
                storeName: $this->storeName,
            ),
            new FinancialTopProductsSheet(
                topProducts: $this->topProducts,
                dateFrom: $this->dateFrom,
                dateTo: $this->dateTo,
                storeName: $this->storeName,
            ),
            new FinancialMetricsSheet($this->metrics, $this->dateFrom, $this->dateTo, $this->storeName),
            new ProductivitySheet(
                products: $this->productivityProducts ?? collect(),
                summary: $this->productivitySummary,
                dateFrom: $this->dateFrom,
                dateTo: $this->dateTo,
                storeName: $this->storeName,
            ),
            new FinancialInfoSheet($this->dateFrom, $this->dateTo, $this->storeName),
        ];
    }
}
