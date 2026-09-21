<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\On;
use App\Models\Sale;
use App\Models\Store;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

new #[Title('Financial Analysis')] class extends Component {
    public $dateFrom;
    public $dateTo;
    public $filterStore = '';

    // Chart-specific filters
    public string $chartPeriod = 'daily';
    public string $chartType = 'line';
    public string $chartStore = '';

    public function mount()
    {
        $this->dateFrom = now()->subDays(30)->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');
    }

    public function setPeriod(string $period): void
    {
        $this->chartPeriod = $period;
        $this->dispatch('chart-updated');
    }

    public function setChartType(string $type): void
    {
        $this->chartType = $type;
        $this->dispatch('chart-updated');
    }

    public function updatedChartStore(): void
    {
        $this->dispatch('chart-updated');
    }

    public function updatedDateFrom(): void
    {
        $this->dispatch('chart-updated');
    }

    public function updatedDateTo(): void
    {
        $this->dispatch('chart-updated');
    }

    protected function buildChartData(Carbon $from, Carbon $to): array
    {
        $storeFilter = $this->chartStore !== '' ? $this->chartStore : $this->filterStore;

        $query = Sale::query()
            ->where('status', 'completed')
            ->whereBetween('sale_date', [$from, $to])
            ->when($storeFilter !== '', fn($q) => $q->where('store_id', $storeFilter));

        $groupExpr = match ($this->chartPeriod) {
            'daily' => 'DATE(sale_date)',
            'weekly' => 'YEARWEEK(sale_date, 1)',
            'monthly' => 'DATE_FORMAT(sale_date, "%Y-%m")',
            'yearly' => 'YEAR(sale_date)',
        };

        $rows = $query
            ->selectRaw(
                "
                {$groupExpr} as period,
                MIN(sale_date) as first_date,
                MAX(sale_date) as last_date,
                MIN(total) as low,
                MAX(total) as high,
                SUM(total) as total_revenue,
                COUNT(*) as transaction_count
            ",
            )
            ->groupByRaw($groupExpr)
            ->orderBy('first_date')
            ->get();

        $data = [];
        foreach ($rows as $row) {
            $firstSale =
                Sale::where('status', 'completed')
                    ->whereBetween('sale_date', [$from, $to])
                    ->when($storeFilter !== '', fn($q) => $q->where('store_id', $storeFilter))
                    ->where('sale_date', $row->first_date)
                    ->value('total') ?? 0;

            $lastSale =
                Sale::where('status', 'completed')
                    ->whereBetween('sale_date', [$from, $to])
                    ->when($storeFilter !== '', fn($q) => $q->where('store_id', $storeFilter))
                    ->where('sale_date', $row->last_date)
                    ->value('total') ?? 0;

            $data[] = [
                'label' => $this->formatPeriodLabel($row->first_date),
                'o' => (float) $firstSale,
                'h' => (float) $row->high,
                'l' => (float) $row->low,
                'c' => (float) $lastSale,
                'revenue' => (float) $row->total_revenue,
                'count' => (int) $row->transaction_count,
            ];
        }

        return $data;
    }

    protected function formatPeriodLabel($date): string
    {
        $carbon = Carbon::parse($date);

        return match ($this->chartPeriod) {
            'daily' => $carbon->format('d M'),
            'weekly' => 'W' . $carbon->weekOfYear . ' ' . $carbon->format('Y'),
            'monthly' => $carbon->format('M Y'),
            'yearly' => $carbon->format('Y'),
        };
    }

    public function with()
    {
        $from = Carbon::parse($this->dateFrom)->startOfDay();
        $to = Carbon::parse($this->dateTo)->endOfDay();

        $saleQuery = Sale::query()
            ->where('status', 'completed')
            ->whereBetween('sale_date', [$from, $to])
            ->when($this->filterStore !== '', fn($q) => $q->where('store_id', $this->filterStore));

        // Chart data — pakai chartStore kalau ada
        $chartData = $this->buildChartData($from, $to);

        // ... semua metrics yang sudah ada, pakai $saleQuery ...

        $totalRevenue = (clone $saleQuery)->sum('total');
        $transactionCount = (clone $saleQuery)->count();

        // HPP
        $totalCogs = (float) DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->join('products', 'products.id', '=', 'sale_items.product_id')
            ->where('sales.status', 'completed')
            ->whereBetween('sales.sale_date', [$from, $to])
            ->when($this->filterStore !== '', fn($q) => $q->where('sales.store_id', $this->filterStore))
            ->sum(DB::raw('products.initial_price * sale_items.quantity'));

        $totalItemsSold = (int) DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->where('sales.status', 'completed')
            ->whereBetween('sales.sale_date', [$from, $to])
            ->when($this->filterStore !== '', fn($q) => $q->where('sales.store_id', $this->filterStore))
            ->sum('sale_items.quantity');

        $grossProfit = $totalRevenue - $totalCogs;
        $grossMarginPct = $totalRevenue > 0 ? ($grossProfit / $totalRevenue) * 100 : 0;
        $operationalCost = $totalRevenue * 0.1;
        $netProfit = $grossProfit - $operationalCost;
        $netMarginPct = $totalRevenue > 0 ? ($netProfit / $totalRevenue) * 100 : 0;
        $markupPct = $totalCogs > 0 ? (($totalRevenue - $totalCogs) / $totalCogs) * 100 : 0;

        $avgSellingPrice = $totalItemsSold > 0 ? $totalRevenue / $totalItemsSold : 0;
        $avgCogsPerItem = $totalItemsSold > 0 ? $totalCogs / $totalItemsSold : 0;
        $contributionMarginPerUnit = $avgSellingPrice - $avgCogsPerItem;
        $bepUnits = $contributionMarginPerUnit > 0 ? ceil($operationalCost / $contributionMarginPerUnit) : 0;
        $bepRevenue = $bepUnits * $avgSellingPrice;

        $topProducts = DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->join('products', 'products.id', '=', 'sale_items.product_id')
            ->where('sales.status', 'completed')
            ->whereBetween('sales.sale_date', [$from, $to])
            ->when($this->filterStore !== '', fn($q) => $q->where('sales.store_id', $this->filterStore))
            ->selectRaw(
                '
                sale_items.product_id,
                sale_items.product_name,
                SUM(sale_items.quantity) as total_qty,
                SUM(sale_items.line_total) as total_revenue,
                SUM(products.initial_price * sale_items.quantity) as total_cogs,
                (SUM(sale_items.line_total) - SUM(products.initial_price * sale_items.quantity)) as gross_profit
            ',
            )
            ->groupBy('sale_items.product_id', 'sale_items.product_name')
            ->orderByDesc('gross_profit')
            ->take(5)
            ->get()
            ->map(function ($row) {
                $row->margin_pct = $row->total_revenue > 0 ? ($row->gross_profit / $row->total_revenue) * 100 : 0;
                return $row;
            });

        return [
            'stores' => Store::orderBy('name')->get(),
            'metrics' => compact('totalRevenue', 'totalCogs', 'grossProfit', 'grossMarginPct', 'operationalCost', 'netProfit', 'netMarginPct', 'markupPct', 'transactionCount', 'totalItemsSold', 'avgSellingPrice', 'avgCogsPerItem', 'contributionMarginPerUnit', 'bepUnits', 'bepRevenue'),
            'topProducts' => $topProducts,
            'chartData' => $chartData,
        ];
    }
};
?>

<div>
    <x-page-header title="Financial Analysis" leading="Analisis margin & kesehatan keuangan" />

    <div class="mx-auto mt-2 max-w-7xl space-y-3">

        <div x-data="salesChart({
            data: @js($chartData),
            type: @js($chartType),
        })" x-init="init()"
            class="rounded-xl border border-stone-200 bg-white p-4 dark:border-stone-800 dark:bg-stone-900">

            {{-- Header --}}
            <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                <div class="flex items-center gap-2">
                    <div class="bg-blue-100 dark:bg-blue-900/60 flex h-7 w-7 items-center justify-center rounded-lg">
                        <svg class="text-blue-700 dark:text-blue-400 h-3.5 w-3.5" fill="none" stroke="currentColor"
                            stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-sm font-semibold text-stone-800 dark:text-stone-100">Tren Penjualan</h2>
                        <p class="text-[10px] text-stone-500 dark:text-stone-400">
                            Visualisasi peningkatan revenue berdasarkan periode
                        </p>
                    </div>
                </div>

                {{-- Chart Type Switcher --}}
                <div
                    class="flex items-center gap-1 rounded-lg border border-stone-200 bg-stone-50 p-0.5 dark:border-stone-700 dark:bg-stone-800">
                    <button wire:click="setChartType('candlestick')" type="button"
                        class="cursor-pointer rounded-md px-2 py-1 text-[10px] font-medium transition
                        {{ $chartType === 'candlestick'
                            ? 'bg-white text-sage-700 shadow-sm dark:bg-stone-700 dark:text-sage-400'
                            : 'text-stone-500 hover:text-stone-700 dark:text-stone-400 dark:hover:text-stone-200' }}">
                        <span class="flex items-center gap-1">
                            <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3 4v16M3 9h4m-4 6h4m-4-3h4m13-8v16m0-13h-4m4 6h-4m4-3h-4" />
                            </svg>
                            Candle
                        </span>
                    </button>
                    <button wire:click="setChartType('line')" type="button"
                        class="cursor-pointer rounded-md px-2 py-1 text-[10px] font-medium transition
                        {{ $chartType === 'line'
                            ? 'bg-white text-sage-700 shadow-sm dark:bg-stone-700 dark:text-sage-400'
                            : 'text-stone-500 hover:text-stone-700 dark:text-stone-400 dark:hover:text-stone-200' }}">
                        <span class="flex items-center gap-1">
                            <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 17l6-6 4 4 8-8" />
                            </svg>
                            Line
                        </span>
                    </button>
                    <button wire:click="setChartType('bar')" type="button"
                        class="cursor-pointer rounded-md px-2 py-1 text-[10px] font-medium transition
                        {{ $chartType === 'bar'
                            ? 'bg-white text-sage-700 shadow-sm dark:bg-stone-700 dark:text-sage-400'
                            : 'text-stone-500 hover:text-stone-700 dark:text-stone-400 dark:hover:text-stone-200' }}">
                        <span class="flex items-center gap-1">
                            <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3 20h18M7 20V10m5 10V6m5 14v-8" />
                            </svg>
                            Bar
                        </span>
                    </button>
                </div>
            </div>

            <div class="mb-3 flex flex-col md:flex-row flex-wrap items-start md:items-center md:justify-between justify-start gap-2">
                {{-- Period Switcher --}}
                <div
                    class="flex flex-1 items-center gap-1 rounded-lg border border-stone-200 bg-stone-50 p-0.5 dark:border-stone-700 dark:bg-stone-800">
                    @foreach (['daily' => 'Hari', 'weekly' => 'Minggu', 'monthly' => 'Bulan', 'yearly' => 'Tahun'] as $period => $label)
                        <button wire:click="setPeriod('{{ $period }}')" type="button"
                            wire:key="period-{{ $period }}"
                            class="cursor-pointer flex-1 rounded-md px-2 py-1.5 text-[10px] font-medium transition
                                {{ $chartPeriod === $period
                                    ? 'bg-white text-sage-700 shadow-sm dark:bg-stone-700 dark:text-sage-400'
                                    : 'text-stone-500 hover:text-stone-700 dark:text-stone-400 dark:hover:text-stone-200' }}">
                            Per {{ $label }}
                        </button>
                    @endforeach
                </div>
                <select wire:model.live="chartStore"
                    class="focus:ring-sage-500 rounded-lg border border-stone-200 bg-stone-50 px-2.5 py-1.5 text-[10px] text-stone-700 dark:border-stone-700 dark:bg-stone-800 dark:text-stone-300">
                    <option value="">Semua Store</option>
                    @foreach ($stores as $store)
                        <option value="{{ $store->id }}">{{ $store->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Chart Container --}}
            <div x-ref="chart" wire:key="chart-{{ $chartType }}-{{ $chartPeriod }}" class="w-full"></div>
        </div>

        {{-- ── FILTER PERIODE ── --}}
        <div class="rounded-xl border border-stone-200 bg-white px-3 py-3 dark:border-stone-800 dark:bg-stone-900">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center">

                <div class="flex items-center gap-2">
                    <label class="text-[11px] font-medium text-stone-600 dark:text-stone-400">Periode:</label>
                    <input wire:model.live="dateFrom" type="date"
                        class="focus:ring-sage-500 rounded-lg border border-stone-200 bg-stone-50 px-2.5 py-1.5 text-xs text-stone-700 dark:border-stone-700 dark:bg-stone-800 dark:text-stone-300" />
                    <span class="text-stone-400">s/d</span>
                    <input wire:model.live="dateTo" type="date"
                        class="focus:ring-sage-500 rounded-lg border border-stone-200 bg-stone-50 px-2.5 py-1.5 text-xs text-stone-700 dark:border-stone-700 dark:bg-stone-800 dark:text-stone-300" />
                </div>

                <select wire:model.live="filterStore"
                    class="focus:ring-sage-500 rounded-lg border border-stone-200 bg-stone-50 px-2.5 py-1.5 text-xs text-stone-700 dark:border-stone-700 dark:bg-stone-800 dark:text-stone-300">
                    <option value="">Semua Store</option>
                    @foreach ($stores as $store)
                        <option value="{{ $store->id }}">{{ $store->name }}</option>
                    @endforeach
                </select>

                <div class="ml-auto flex items-center gap-2">
                    <div wire:loading class="text-sage-600 dark:text-sage-400 flex items-center gap-1 text-[11px]">
                        <svg class="h-3 w-3 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4" />
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z" />
                        </svg>
                        {{ __('Menghitung…') }}
                    </div>
                </div>
            </div>
        </div>

        {{-- ════════════════════════════════════════════════ --}}
        {{-- MATRIX 1: GROSS & NET MARGIN                   --}}
        {{-- ════════════════════════════════════════════════ --}}
        <div class="rounded-xl border border-stone-200 bg-white p-4 dark:border-stone-800 dark:bg-stone-900">
            <div class="mb-3 flex items-center gap-2">
                <div class="bg-sage-100 dark:bg-sage-900/60 flex h-7 w-7 items-center justify-center rounded-lg">
                    <svg class="text-sage-700 dark:text-sage-400 h-3.5 w-3.5" fill="none" stroke="currentColor"
                        stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-sm font-semibold text-stone-800 dark:text-stone-100">Profitability Matrix</h2>
                    <p class="text-[10px] text-stone-500 dark:text-stone-400">
                        Untuk <span class="font-semibold">Financial Analyst</span> & <span
                            class="font-semibold">Accountant</span>
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-2 lg:grid-cols-4">

                {{-- Revenue --}}
                <div
                    class="rounded-lg border border-stone-200 bg-stone-50 p-3 dark:border-stone-700 dark:bg-stone-800/50">
                    <p class="text-[10px] uppercase tracking-wider text-stone-500 dark:text-stone-400">Penjualan</p>
                    <p class="mt-1 font-mono text-base font-semibold text-stone-800 dark:text-stone-100">
                        Rp {{ number_format($metrics['totalRevenue'], 0, ',', '.') }}
                    </p>
                    <p class="mt-0.5 text-[10px] text-stone-400">{{ $metrics['transactionCount'] }} transaksi ·
                        {{ $metrics['totalItemsSold'] }} item</p>
                </div>

                {{-- COGS --}}
                <div
                    class="rounded-lg border border-stone-200 bg-stone-50 p-3 dark:border-stone-700 dark:bg-stone-800/50">
                    <p class="text-[10px] uppercase tracking-wider text-stone-500 dark:text-stone-400">HPP (COGS)</p>
                    <p class="mt-1 font-mono text-base font-semibold text-amber-700 dark:text-amber-400">
                        Rp {{ number_format($metrics['totalCogs'], 0, ',', '.') }}
                    </p>
                    <p class="mt-0.5 text-[10px] text-stone-400">
                        {{ $metrics['totalRevenue'] > 0 ? number_format(($metrics['totalCogs'] / $metrics['totalRevenue']) * 100, 1) : 0 }}%
                        dari revenue
                    </p>
                </div>

                {{-- Gross Profit --}}
                <div
                    class="rounded-lg border border-stone-200 bg-stone-50 p-3 dark:border-stone-700 dark:bg-stone-800/50">
                    <p class="text-[10px] uppercase tracking-wider text-stone-500 dark:text-stone-400">Laba Kotor</p>
                    <p
                        class="mt-1 font-mono text-base font-semibold {{ $metrics['grossProfit'] >= 0 ? 'text-green-700 dark:text-green-400' : 'text-red-700 dark:text-red-400' }}">
                        Rp {{ number_format($metrics['grossProfit'], 0, ',', '.') }}
                    </p>
                    <p
                        class="mt-0.5 text-[10px] font-semibold {{ $metrics['grossProfit'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ number_format($metrics['grossMarginPct'], 2) }}% margin
                    </p>
                </div>

                {{-- Net Profit --}}
                <div
                    class="rounded-lg border border-stone-200 bg-stone-50 p-3 dark:border-stone-700 dark:bg-stone-800/50">
                    <p class="text-[10px] uppercase tracking-wider text-stone-500 dark:text-stone-400">Laba Bersih</p>
                    <p
                        class="mt-1 font-mono text-base font-semibold {{ $metrics['netProfit'] >= 0 ? 'text-green-700 dark:text-green-400' : 'text-red-700 dark:text-red-400' }}">
                        Rp {{ number_format($metrics['netProfit'], 0, ',', '.') }}
                    </p>
                    <p
                        class="mt-0.5 text-[10px] font-semibold {{ $metrics['netProfit'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ number_format($metrics['netMarginPct'], 2) }}% margin
                    </p>
                </div>
            </div>

            {{-- Formula hint --}}
            <div
                class="mt-3 space-y-1 rounded-md border border-stone-100 bg-stone-50/50 p-2.5 dark:border-stone-800 dark:bg-stone-800/30">
                <p class="font-mono text-[10px] text-stone-600 dark:text-stone-400">
                    <span class="font-semibold">Gross Margin</span> = ((Penjualan − HPP) / Penjualan) × 100%
                    = (({{ number_format($metrics['totalRevenue'], 0, ',', '.') }} −
                    {{ number_format($metrics['totalCogs'], 0, ',', '.') }}) /
                    {{ number_format($metrics['totalRevenue'], 0, ',', '.') }}) × 100%
                    = <span
                        class="font-bold text-sage-700 dark:text-sage-400">{{ number_format($metrics['grossMarginPct'], 2) }}%</span>
                </p>
                <p class="font-mono text-[10px] text-stone-600 dark:text-stone-400">
                    <span class="font-semibold">Net Margin</span> = ((Penjualan − Total Biaya) / Penjualan) × 100%
                    = (({{ number_format($metrics['totalRevenue'], 0, ',', '.') }} −
                    {{ number_format($metrics['totalCogs'] + $metrics['operationalCost'], 0, ',', '.') }}) /
                    {{ number_format($metrics['totalRevenue'], 0, ',', '.') }}) × 100%
                    = <span
                        class="font-bold text-sage-700 dark:text-sage-400">{{ number_format($metrics['netMarginPct'], 2) }}%</span>
                </p>
            </div>
        </div>

        {{-- ════════════════════════════════════════════════ --}}
        {{-- MATRIX 2: PRICING & BREAK-EVEN                 --}}
        {{-- ════════════════════════════════════════════════ --}}
        <div class="rounded-xl border border-stone-200 bg-white p-4 dark:border-stone-800 dark:bg-stone-900">
            <div class="mb-3 flex items-center gap-2">
                <div class="bg-blue-100 dark:bg-blue-900/60 flex h-7 w-7 items-center justify-center rounded-lg">
                    <svg class="text-blue-700 dark:text-blue-400 h-3.5 w-3.5" fill="none" stroke="currentColor"
                        stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-sm font-semibold text-stone-800 dark:text-stone-100">Pricing & Break-Even Matrix
                    </h2>
                    <p class="text-[10px] text-stone-500 dark:text-stone-400">
                        Untuk <span class="font-semibold">Pricing Strategist</span> & <span class="font-semibold">Cost
                            Estimator</span>
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-2 lg:grid-cols-4">

                {{-- Markup % --}}
                <div
                    class="rounded-lg border border-stone-200 bg-stone-50 p-3 dark:border-stone-700 dark:bg-stone-800/50">
                    <p class="text-[10px] uppercase tracking-wider text-stone-500 dark:text-stone-400">Markup</p>
                    <p class="mt-1 font-mono text-base font-semibold text-blue-700 dark:text-blue-400">
                        {{ number_format($metrics['markupPct'], 2) }}%
                    </p>
                    <p class="mt-0.5 text-[10px] text-stone-400">di atas HPP</p>
                </div>

                {{-- Avg Price --}}
                <div
                    class="rounded-lg border border-stone-200 bg-stone-50 p-3 dark:border-stone-700 dark:bg-stone-800/50">
                    <p class="text-[10px] uppercase tracking-wider text-stone-500 dark:text-stone-400">Harga Rata-rata
                    </p>
                    <p class="mt-1 font-mono text-base font-semibold text-stone-800 dark:text-stone-100">
                        Rp {{ number_format($metrics['avgSellingPrice'], 0, ',', '.') }}
                    </p>
                    <p class="mt-0.5 text-[10px] text-stone-400">per item terjual</p>
                </div>

                {{-- Contribution Margin --}}
                <div
                    class="rounded-lg border border-stone-200 bg-stone-50 p-3 dark:border-stone-700 dark:bg-stone-800/50">
                    <p class="text-[10px] uppercase tracking-wider text-stone-500 dark:text-stone-400">Kontribusi/Item
                    </p>
                    <p class="mt-1 font-mono text-base font-semibold text-green-700 dark:text-green-400">
                        Rp {{ number_format($metrics['contributionMarginPerUnit'], 0, ',', '.') }}
                    </p>
                    <p class="mt-0.5 text-[10px] text-stone-400">harga − HPP</p>
                </div>

                {{-- BEP --}}
                <div
                    class="rounded-lg border border-stone-200 bg-stone-50 p-3 dark:border-stone-700 dark:bg-stone-800/50">
                    <p class="text-[10px] uppercase tracking-wider text-stone-500 dark:text-stone-400">Break-Even Point
                    </p>
                    <p class="mt-1 font-mono text-base font-semibold text-amber-700 dark:text-amber-400">
                        {{ number_format($metrics['bepUnits'], 0, ',', '.') }} unit
                    </p>
                    <p class="mt-0.5 text-[10px] text-stone-400">
                        ≈ Rp {{ number_format($metrics['bepRevenue'], 0, ',', '.') }}
                    </p>
                </div>
            </div>

            <div
                class="mt-3 space-y-1 rounded-md border border-stone-100 bg-stone-50/50 p-2.5 dark:border-stone-800 dark:bg-stone-800/30">
                <p class="font-mono text-[10px] text-stone-600 dark:text-stone-400">
                    <span class="font-semibold">Markup</span> = ((Harga Jual − HPP) / HPP) × 100%
                    = (({{ number_format($metrics['avgSellingPrice'], 0, ',', '.') }} −
                    {{ number_format($metrics['avgCogsPerItem'], 0, ',', '.') }}) /
                    {{ number_format($metrics['avgCogsPerItem'], 0, ',', '.') }}) × 100%
                    = <span
                        class="font-bold text-blue-700 dark:text-blue-400">{{ number_format($metrics['markupPct'], 2) }}%</span>
                </p>
                <p class="font-mono text-[10px] text-stone-600 dark:text-stone-400">
                    <span class="font-semibold">BEP</span> = Fixed Cost / (Harga − Variable Cost)
                    = {{ number_format($metrics['operationalCost'], 0, ',', '.') }} /
                    ({{ number_format($metrics['avgSellingPrice'], 0, ',', '.') }} −
                    {{ number_format($metrics['avgCogsPerItem'], 0, ',', '.') }})
                    = <span
                        class="font-bold text-amber-700 dark:text-amber-400">{{ number_format($metrics['bepUnits'], 0, ',', '.') }}
                        unit</span>
                </p>
            </div>
        </div>

        {{-- ════════════════════════════════════════════════ --}}
        {{-- TOP PRODUCTS BY MARGIN                        --}}
        {{-- ════════════════════════════════════════════════ --}}
        <div class="rounded-xl border border-stone-200 bg-white p-4 dark:border-stone-800 dark:bg-stone-900">
            <div class="mb-3 flex items-center gap-2">
                <div class="bg-purple-100 dark:bg-purple-900/60 flex h-7 w-7 items-center justify-center rounded-lg">
                    <svg class="text-purple-700 dark:text-purple-400 h-3.5 w-3.5" fill="none"
                        stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M16.5 18.75h-9m9 0a3 3 0 013 3h-15a3 3 0 013-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 01-.982-3.172M9.497 14.25a7.454 7.454 0 00.981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 007.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M7.73 9.728a6.726 6.726 0 002.748 1.35m8.272-6.842V4.5c0 2.108-.966 3.99-2.48 5.228m2.48-5.492a46.32 46.32 0 012.916.52 6.003 6.003 0 01-5.395 4.972m0 0a6.726 6.726 0 01-2.749 1.35m0 0a6.772 6.772 0 01-3.044 0" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-sm font-semibold text-stone-800 dark:text-stone-100">Top 5 Produk — Kontribusi Laba
                    </h2>
                    <p class="text-[10px] text-stone-500 dark:text-stone-400">
                        Untuk <span class="font-semibold">Cost Estimator</span> — prioritaskan produk dengan margin
                        tertinggi
                    </p>
                </div>
            </div>

            <div class="overflow-x-auto rounded-lg border border-stone-200 dark:border-stone-700">
                <table class="w-full text-left text-[11px]">
                    <thead>
                        <tr
                            class="border-b border-stone-200 bg-stone-50 font-semibold uppercase tracking-wider text-stone-500 dark:border-stone-700 dark:bg-stone-800/50 dark:text-stone-400">
                            <th class="px-3 py-1.5">{{ __('Produk') }}</th>
                            <th class="px-3 py-1.5 text-right">{{ __('Qty') }}</th>
                            <th class="px-3 py-1.5 text-right">{{ __('Revenue') }}</th>
                            <th class="px-3 py-1.5 text-right">{{ __('HPP') }}</th>
                            <th class="px-3 py-1.5 text-right">{{ __('Laba Kotor') }}</th>
                            <th class="px-3 py-1.5 text-right">{{ __('Margin') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100 dark:divide-stone-800/60">
                        @forelse ($topProducts as $product)
                            <tr class="transition-colors hover:bg-stone-50 dark:hover:bg-stone-800/30">
                                <td class="px-3 py-1.5 font-medium text-stone-800 dark:text-stone-200">
                                    {{ $product->product_name }}
                                </td>
                                <td class="px-3 py-1.5 text-right font-mono text-stone-600 dark:text-stone-400">
                                    {{ number_format($product->total_qty, 0, ',', '.') }}
                                </td>
                                <td class="px-3 py-1.5 text-right font-mono text-stone-700 dark:text-stone-300">
                                    Rp {{ number_format($product->total_revenue, 0, ',', '.') }}
                                </td>
                                <td class="px-3 py-1.5 text-right font-mono text-amber-700 dark:text-amber-400">
                                    Rp {{ number_format($product->total_cogs, 0, ',', '.') }}
                                </td>
                                <td
                                    class="px-3 py-1.5 text-right font-mono font-semibold text-green-700 dark:text-green-400">
                                    Rp {{ number_format($product->gross_profit, 0, ',', '.') }}
                                </td>
                                <td class="px-3 py-1.5 text-right">
                                    <span
                                        class="inline-flex items-center rounded-full bg-sage-50 px-2 py-0.5 font-mono text-[10px] font-semibold text-sage-700 dark:bg-sage-950/40 dark:text-sage-400">
                                        {{ number_format($product->margin_pct, 1) }}%
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-3 py-6 text-center text-[11px] text-stone-400">
                                    {{ __('Tidak ada data produk pada periode ini') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ════════════════════════════════════════════════ --}}
        {{-- GLOSSARY / CHEATSHEET                          --}}
        {{-- ════════════════════════════════════════════════ --}}
        <div class="rounded-xl border border-stone-200 bg-white p-4 dark:border-stone-800 dark:bg-stone-900">
            <h2 class="mb-3 text-sm font-semibold text-stone-800 dark:text-stone-100">
                {{ __('Cheatsheet Formula') }}
            </h2>
            <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-md border border-stone-200 p-2.5 dark:border-stone-700">
                    <p class="text-[10px] font-bold uppercase text-stone-700 dark:text-stone-300">Gross Margin</p>
                    <p class="mt-1 font-mono text-[10px] text-stone-600 dark:text-stone-400">
                        (Penjualan − HPP) / Penjualan × 100%
                    </p>
                </div>
                <div class="rounded-md border border-stone-200 p-2.5 dark:border-stone-700">
                    <p class="text-[10px] font-bold uppercase text-stone-700 dark:text-stone-300">Net Margin</p>
                    <p class="mt-1 font-mono text-[10px] text-stone-600 dark:text-stone-400">
                        (Penjualan − Total Biaya) / Penjualan × 100%
                    </p>
                </div>
                <div class="rounded-md border border-stone-200 p-2.5 dark:border-stone-700">
                    <p class="text-[10px] font-bold uppercase text-stone-700 dark:text-stone-300">Markup</p>
                    <p class="mt-1 font-mono text-[10px] text-stone-600 dark:text-stone-400">
                        (Harga − HPP) / HPP × 100%
                    </p>
                </div>
                <div class="rounded-md border border-stone-200 p-2.5 dark:border-stone-700">
                    <p class="text-[10px] font-bold uppercase text-stone-700 dark:text-stone-300">Break-Even</p>
                    <p class="mt-1 font-mono text-[10px] text-stone-600 dark:text-stone-400">
                        Fixed Cost / (Harga − Var. Cost)
                    </p>
                </div>
            </div>
        </div>

    </div>
</div>
@script
    <script>
        Alpine.data('salesChart', ({
            data,
            type
        }) => ({
            chart: null,
            data: data,
            type: type,
            _observer: null,

            init() {
                this.$nextTick(() => {
                    this.render();
                });

                // Watch perubahan data (kalau Livewire update tanpa ganti key)
                this.$watch('data', () => this.render());

                // Dark mode observer
                this._observer = new MutationObserver(() => this.render());
                this._observer.observe(document.documentElement, {
                    attributes: true,
                    attributeFilter: ['class'],
                });
            },

            destroy() {
                if (this.chart) {
                    this.chart.destroy();
                    this.chart = null;
                }
                if (this._observer) {
                    this._observer.disconnect();
                    this._observer = null;
                }
            },

            render() {
                if (this.chart) {
                    this.chart.destroy();
                    this.chart = null;
                }

                const container = this.$refs.chart;
                if (!container) return;
                container.innerHTML = '';

                if (!this.data || this.data.length === 0) {
                    container.innerHTML =
                        '<div class="flex h-[320px] items-center justify-center text-xs text-stone-400">Tidak ada data pada periode ini</div>';
                    return;
                }

                this.chart = new ApexCharts(container, this.buildOptions(this.buildSeries()));
                this.chart.render();
            },

            buildSeries() {
                if (this.type === 'candlestick') {
                    return [{
                        name: 'Revenue',
                        data: this.data.map(d => ({
                            x: d.label,
                            y: [d.o, d.h, d.l, d.c],
                        })),
                    }];
                }

                return [{
                    name: 'Revenue',
                    data: this.data.map(d => ({
                        x: d.label,
                        y: d.revenue,
                    })),
                }];
            },

            buildOptions(series) {
                const isCandle = this.type === 'candlestick';
                const isBar = this.type === 'bar';
                const isDark = document.documentElement.classList.contains('dark');

                return {
                    series: series,
                    chart: {
                        type: this.type,
                        height: 320,
                        width: '100%',
                        parentHeightOffset: 0, // ← hilangkan padding default
                        redrawOnParentResize: true, // ← redraw saat parent resize
                        redrawOnWindowResize: true,
                        toolbar: {
                            show: false
                        },
                        fontFamily: 'inherit',
                        background: 'transparent',
                        animations: {
                            enabled: true,
                            speed: 400
                        },
                    },
                    theme: {
                        mode: isDark ? 'dark' : 'light'
                    },
                    plotOptions: {
                        candlestick: {
                            colors: {
                                upward: '#16a34a',
                                downward: '#dc2626'
                            },
                            wick: {
                                useFillColor: true
                            },
                        },
                        bar: {
                            columnWidth: '60%',
                            borderRadius: 3
                        },
                    },
                    stroke: {
                        width: isBar ? 0 : (isCandle ? 1 : 2),
                        curve: 'smooth',
                    },
                    colors: isCandle ? undefined : ['#84a98c'],
                    dataLabels: {
                        enabled: false
                    },
                    markers: {
                        size: isBar || isCandle ? 0 : 4,
                        colors: ['#84a98c'],
                        strokeColors: isDark ? '#1c1917' : '#fff',
                        strokeWidth: 2,
                    },
                    xaxis: {
                        type: 'category',
                        labels: {
                            style: {
                                fontSize: '10px',
                                colors: isDark ? '#a8a29e' : '#78716c'
                            },
                            rotate: -45,
                            rotateAlways: false,
                            trim: true,
                        },
                        axisBorder: {
                            show: false
                        },
                        axisTicks: {
                            show: false
                        },
                    },
                    yaxis: {
                        labels: {
                            style: {
                                fontSize: '10px',
                                colors: isDark ? '#a8a29e' : '#78716c'
                            },
                            formatter: (val) => {
                                if (Math.abs(val) >= 1_000_000) return 'Rp ' + (val / 1_000_000).toFixed(
                                    1) + 'jt';
                                if (Math.abs(val) >= 1_000) return 'Rp ' + (val / 1_000).toFixed(0) + 'rb';
                                return 'Rp ' + val;
                            },
                        },
                    },
                    grid: {
                        borderColor: isDark ? '#292524' : '#e7e5e4',
                        strokeDashArray: 4,
                        xaxis: {
                            lines: {
                                show: false
                            }
                        },
                        yaxis: {
                            lines: {
                                show: true
                            }
                        },
                        padding: {
                            left: 8,
                            right: 8
                        },
                    },
                    tooltip: {
                        theme: isDark ? 'dark' : 'light',
                        y: {
                            formatter: (val, opts) => {
                                if (isCandle) {
                                    const point = opts.w.config.series[opts.seriesIndex].data[opts
                                        .dataPointIndex];
                                    const [o, h, l, c] = point.y;
                                    const fmt = (n) => 'Rp ' + Number(n).toLocaleString('id-ID');
                                    return [
                                        `Open: ${fmt(o)}`,
                                        `High: ${fmt(h)}`,
                                        `Low: ${fmt(l)}`,
                                        `Close: ${fmt(c)}`,
                                    ].join(' | ');
                                }
                                return 'Rp ' + Number(val).toLocaleString('id-ID');
                            },
                        },
                    },
                };
            },
        }));
    </script>
@endscript
