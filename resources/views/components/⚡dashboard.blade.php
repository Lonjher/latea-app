<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use App\Models\Sale;
use App\Models\Store;
use App\Models\SaleItem;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

new #[Title('Dashboard')] class extends Component {
    public $filterStore = '';

    public function with()
    {
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();

        // ── Base query hari ini ──
        $todayQuery = Sale::query()
            ->where('status', 'completed')
            ->whereDate('sale_date', $today)
            ->when($this->filterStore !== '', fn($q) => $q->where('store_id', $this->filterStore));

        // ── PENDAPATAN HARI INI ──
        $todayRevenue = (clone $todayQuery)->sum('total');
        $todayTransactions = (clone $todayQuery)->count();
        $todayItemsSold = (int) DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->where('sales.status', 'completed')
            ->whereDate('sales.sale_date', $today)
            ->when($this->filterStore !== '', fn($q) => $q->where('sales.store_id', $this->filterStore))
            ->sum('sale_items.quantity');

        // ── PENDAPATAN KEMARIN (untuk perbandingan) ──
        $yesterdayRevenue = Sale::query()
            ->where('status', 'completed')
            ->whereDate('sale_date', $yesterday)
            ->when($this->filterStore !== '', fn($q) => $q->where('store_id', $this->filterStore))
            ->sum('total');

        // Hitung growth %
        $revenueGrowth = $yesterdayRevenue > 0 ? (($todayRevenue - $yesterdayRevenue) / $yesterdayRevenue) * 100 : ($todayRevenue > 0 ? 100 : 0);

        // ── PRODUK TERJUAL HARI INI (terurut dari paling banyak) ──
        $topProducts = DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->where('sales.status', 'completed')
            ->whereDate('sales.sale_date', $today)
            ->when($this->filterStore !== '', fn($q) => $q->where('sales.store_id', $this->filterStore))
            ->selectRaw(
                '
                sale_items.product_id,
                sale_items.product_name,
                SUM(sale_items.quantity) as total_qty,
                SUM(sale_items.line_total) as total_revenue,
                COUNT(DISTINCT sale_items.sale_id) as transaction_count
            ',
            )
            ->groupBy('sale_items.product_id', 'sale_items.product_name')
            ->orderByDesc('total_qty')
            ->take(10)
            ->get();

        // ── TRANSAKSI TERBARU HARI INI ──
        $recentSales = (clone $todayQuery)
            ->with(['store', 'items'])
            ->orderByDesc('sale_date')
            ->take(5)
            ->get();

        // ── BREAKDOWN PER STORE HARI INI ──
        $storeBreakdown = Sale::query()
            ->where('status', 'completed')
            ->whereDate('sale_date', $today)
            ->when($this->filterStore !== '', fn($q) => $q->where('store_id', $this->filterStore))
            ->selectRaw('store_id, SUM(total) as revenue, COUNT(*) as transactions')
            ->groupBy('store_id')
            ->with('store')
            ->orderByDesc('revenue')
            ->get();

        return [
            'stores' => Store::orderBy('name')->get(),
            'metrics' => [
                'todayRevenue' => $todayRevenue,
                'todayTransactions' => $todayTransactions,
                'todayItemsSold' => $todayItemsSold,
                'yesterdayRevenue' => $yesterdayRevenue,
                'revenueGrowth' => $revenueGrowth,
                'avgTransaction' => $todayTransactions > 0 ? $todayRevenue / $todayTransactions : 0,
            ],
            'topProducts' => $topProducts,
            'recentSales' => $recentSales,
            'storeBreakdown' => $storeBreakdown,
        ];
    }
};
?>
<div>
    <x-page-header title="Dashboard" leading="Statistik penjualan hari ini" time="true" />

    <div class="mx-auto mt-2 max-w-7xl space-y-3">

        {{-- ── FILTER STORE ── --}}
        <div class="rounded-xl border border-stone-200 bg-white px-3 py-3 dark:border-stone-800 dark:bg-stone-900">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-2">
                    <div class="bg-sage-100 dark:bg-sage-900/60 flex h-7 w-7 items-center justify-center rounded-lg">
                        <svg class="text-sage-700 dark:text-sage-400 h-3.5 w-3.5" fill="none" stroke="currentColor"
                            stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-stone-800 dark:text-stone-100">
                            {{ now()->translatedFormat('l, d F Y') }}
                        </p>
                        <p class="text-[10px] text-stone-500 dark:text-stone-400">Data penjualan hari ini</p>
                    </div>
                </div>

                <select wire:model.live="filterStore"
                    class="focus:ring-sage-500 w-full rounded-lg border border-stone-200 bg-stone-50 px-2.5 py-1.5 text-xs text-stone-700 dark:border-stone-700 dark:bg-stone-800 dark:text-stone-300 sm:w-auto">
                    <option value="">Semua Store</option>
                    @foreach ($stores as $store)
                        <option value="{{ $store->id }}">{{ $store->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- ════════════════════════════════════════════════ --}}
        {{-- METRICS CARDS                                  --}}
        {{-- ════════════════════════════════════════════════ --}}
        <div class="grid grid-cols-2 gap-2 lg:grid-cols-4">

            {{-- Pendapatan Hari Ini --}}
            <div class="rounded-xl border border-stone-200 bg-white p-3 dark:border-stone-800 dark:bg-stone-900">
                <div class="flex items-start justify-between">
                    <div class="min-w-0 flex-1">
                        <p class="text-[10px] font-medium uppercase tracking-wider text-stone-500 dark:text-stone-400">
                            {{ __('Pendapatan') }}
                        </p>
                        <p class="mt-1 truncate font-mono text-lg font-semibold text-sage-700 dark:text-sage-400">
                            Rp {{ number_format($metrics['todayRevenue'], 0, ',', '.') }}
                        </p>
                        @if ($metrics['todayRevenue'] > 0 || $metrics['yesterdayRevenue'] > 0)
                            <div class="mt-1 flex items-center gap-1">
                                @if ($metrics['revenueGrowth'] >= 0)
                                    <svg class="h-3 w-3 text-green-600 dark:text-green-400" fill="none"
                                        stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M4.5 15.75l7.5-7.5 7.5 7.5" />
                                    </svg>
                                    <span class="text-[10px] font-semibold text-green-600 dark:text-green-400">
                                        +{{ number_format($metrics['revenueGrowth'], 1) }}%
                                    </span>
                                @else
                                    <svg class="h-3 w-3 text-red-600 dark:text-red-400" fill="none"
                                        stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                    </svg>
                                    <span class="text-[10px] font-semibold text-red-600 dark:text-red-400">
                                        {{ number_format($metrics['revenueGrowth'], 1) }}%
                                    </span>
                                @endif
                                <span class="text-[10px] text-stone-400 dark:text-stone-500">vs kemarin</span>
                            </div>
                        @else
                            <p class="mt-1 text-[10px] text-stone-400">Belum ada data kemarin</p>
                        @endif
                    </div>
                    <div
                        class="bg-sage-100 dark:bg-sage-900/60 flex h-7 w-7 shrink-0 items-center justify-center rounded-lg">
                        <svg class="text-blue-700 dark:text-blue-400 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="currentColor" aria-label="badge dollar sign"
                            viewBox="0 0 166 177">
                            <path
                                d="M73.1 10.6a25 25 0 0 0-8.9 7.6c-3.4 4.8-3.7 5-7.1 4.4-8.7-1.6-10.9-1.7-14.8-.7-9 2.4-14.3 10-14.3 20.3a24 24 0 0 1-.8 7.4c-.4.6-2.7 2.1-5.2 3.4-9.6 4.8-13.4 10.9-12.8 20.6.3 4.9 1.1 7.4 3.7 11.5l3.3 5.4-3.1 5.5c-3.7 6.8-4.7 12.2-3.2 17.8a22 22 0 0 0 12.7 13.7c3 1.4 5.4 2.9 5.5 3.3l.3 7.1q.6 11.3 10.1 16.9c3.7 2.1 4.8 2.3 13.9 1.7l9.8-.5 4.1 4.6c4.9 5.6 8.8 7.4 16.1 7.4s10.2-1.2 15.9-6.8l4.8-4.7h10.8c10 0 11-.2 14.9-2.8q8-5.4 8.2-15.9c0-2.9.4-5.8.8-6.4s3-2.3 5.8-3.7c7-3.6 12.1-9.4 12.9-15 .9-5.9 0-10-3.7-16.5l-3-5.3 3.4-5.2c3.1-4.8 3.3-5.8 3.3-13.3 0-7.7-.2-8.5-3.1-12.1a43 43 0 0 0-14.8-9.6c-.5-.2-1.1-3.7-1.2-7.9-.2-8.2-1.6-11.7-6.1-16-5.7-5.5-11.9-6.8-21.3-4.6l-5.1 1.2-5.4-6.1C90.9 7.7 83 5.7 73.1 10.6m15.2 4.5c1.2.6 5 4 8.3 7.7l6.2 6.6 5.9-1.2c8.6-1.8 12.5-1.5 16.4 1.1 5 3.3 6.9 7.9 6.9 16.3 0 8.2.4 8.7 9.2 12.8 11.9 5.6 13.9 15.8 5.4 26.9q-4.6 6-.7 10.8c5.5 7.1 6.6 14.7 3 20.5q-2 3.3-8.4 6.3a28 28 0 0 0-7.1 4.3c-.5.7-1.1 4.5-1.5 8.5-.4 5.6-1.1 7.9-3.2 10.7a15 15 0 0 1-14.3 5.2l-8.7-1.2c-4.2-.6-4.4-.5-9.7 5-9.7 10-16.8 10.1-26.4.4l-6-6-4.5.7c-9.1 1.5-15.8 1.1-18.8-1.1q-6.2-4.6-6.3-14.5c0-6.9-1.4-8.8-9-12.6s-10.3-8.1-9.8-14.9c.3-3.5 1.4-6.3 3.6-9.6 1.8-2.6 3.2-5.6 3.2-6.8s-1.7-4.9-3.8-8.3Q8.1 66.5 25 58c7.8-3.9 9.1-5.9 8.4-12.9-1.3-13.4 9.1-21.4 22.2-17.1 5.1 1.7 7.9.6 11.9-4.8 5-6.6 9-9.2 14.3-9.2 2.4 0 5.3.5 6.5 1.1">
                            </path>
                            <path
                                d="m80.5 48.2-.7 4.1c-.2 2.4-1 3.1-4.2 4.2C65.3 60 60.5 68.1 62.4 78.3c.9 4.6 6.7 10.2 12.7 12.2l4.9 1.7v13.4c0 11.6-.2 13.4-1.6 13.4-3.1 0-8.6-3.4-11.6-7.1-4.9-6.1-9.3-4.6-6 2 2 4.2 7.1 8 13.5 10.2 4.3 1.5 5.2 2.2 5.3 4.2q.1 4.8 3.4 4.7 3 0 3-5.6 0-2.3 1.5-2.4c4.1 0 12.5-6.2 15.1-11.2a22 22 0 0 0 0-15.1c-1.8-4.4-5.1-7.1-11.9-10.1L85 86V74c0-10.8.2-12 1.8-12 2.2 0 6.4 2.8 9 6 1.7 2 2.4 2.3 3.9 1.4a5 5 0 0 0 2.1-2.7c.5-2.5-6.4-8.6-11.4-9.9-4-1.1-4.4-1.5-4.4-4.3 0-3.3-1.5-5.5-3.7-5.5q-1.3.1-1.8 1.2M80 73.5q0 11.3-1 11.5c-4.2 0-11-6.6-11-10.7 0-5.9 5.3-12.1 10.3-12.3 1.5 0 1.7 1.2 1.7 11.5m15.3 25.3c4.4 4.8 2.6 14.5-3.5 18.2-6.5 3.9-6.8 3.5-6.8-10.3 0-6.8.3-12.6.6-13 .9-.8 7.5 2.6 9.7 5.1">
                            </path>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Transaksi --}}
            <div class="rounded-xl border border-stone-200 bg-white p-3 dark:border-stone-800 dark:bg-stone-900">
                <div class="flex items-start justify-between">
                    <div class="min-w-0 flex-1">
                        <p class="text-[10px] font-medium uppercase tracking-wider text-stone-500 dark:text-stone-400">
                            {{ __('Transaksi') }}
                        </p>
                        <p class="mt-1 font-mono text-lg font-semibold text-stone-800 dark:text-stone-100">
                            {{ number_format($metrics['todayTransactions'], 0, ',', '.') }}
                        </p>
                        <p class="mt-1 text-[10px] text-stone-400">
                            Rata-rata Rp {{ number_format($metrics['avgTransaction'], 0, ',', '.') }}
                        </p>
                    </div>
                    <div
                        class="bg-blue-100 dark:bg-blue-900/60 flex h-7 w-7 shrink-0 items-center justify-center rounded-lg">
                        <svg class="text-blue-700 dark:text-blue-400 h-3.5 w-3.5" fill="none" stroke="currentColor"
                            stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Item Terjual --}}
            <div class="rounded-xl border border-stone-200 bg-white p-3 dark:border-stone-800 dark:bg-stone-900">
                <div class="flex items-start justify-between">
                    <div class="min-w-0 flex-1">
                        <p class="text-[10px] font-medium uppercase tracking-wider text-stone-500 dark:text-stone-400">
                            {{ __('Item Terjual') }}
                        </p>
                        <p class="mt-1 font-mono text-lg font-semibold text-stone-800 dark:text-stone-100">
                            {{ number_format($metrics['todayItemsSold'], 0, ',', '.') }}
                        </p>
                        <p class="mt-1 text-[10px] text-stone-400">pcs hari ini</p>
                    </div>
                    <div
                        class="bg-purple-100 dark:bg-purple-900/60 flex h-7 w-7 shrink-0 items-center justify-center rounded-lg">
                        <svg class="w-4 h-4 text-gray-800 dark:text-white" aria-hidden="true"
                            xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"
                            viewBox="0 0 24 24">
                            <path
                                d="M20 7h-.7c.229-.467.349-.98.351-1.5a3.5 3.5 0 0 0-3.5-3.5c-1.717 0-3.215 1.2-4.331 2.481C10.4 2.842 8.949 2 7.5 2A3.5 3.5 0 0 0 4 5.5c.003.52.123 1.033.351 1.5H4a2 2 0 0 0-2 2v2a1 1 0 0 0 1 1h18a1 1 0 0 0 1-1V9a2 2 0 0 0-2-2Zm-9.942 0H7.5a1.5 1.5 0 0 1 0-3c.9 0 2 .754 3.092 2.122-.219.337-.392.635-.534.878Zm6.1 0h-3.742c.933-1.368 2.371-3 3.739-3a1.5 1.5 0 0 1 0 3h.003ZM13 14h-2v8h2v-8Zm-4 0H4v6a2 2 0 0 0 2 2h3v-8Zm6 0v8h3a2 2 0 0 0 2-2v-6h-5Z" />
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Produk Unik --}}
            <div class="rounded-xl border border-stone-200 bg-white p-3 dark:border-stone-800 dark:bg-stone-900">
                <div class="flex items-start justify-between">
                    <div class="min-w-0 flex-1">
                        <p class="text-[10px] font-medium uppercase tracking-wider text-stone-500 dark:text-stone-400">
                            {{ __('Produk Unik') }}
                        </p>
                        <p class="mt-1 font-mono text-lg font-semibold text-stone-800 dark:text-stone-100">
                            {{ $topProducts->count() }}
                        </p>
                        <p class="mt-1 text-[10px] text-stone-400">produk terjual</p>
                    </div>
                    <div
                        class="bg-amber-100 dark:bg-amber-900/60 flex h-7 w-7 shrink-0 items-center justify-center rounded-lg">
                        <svg class="text-amber-700 dark:text-amber-400 h-3.5 w-3.5" fill="none" stroke="currentColor"
                            stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- ════════════════════════════════════════════════ --}}
        {{-- PRODUK TERJUAL & RECENT SALES                 --}}
        {{-- ════════════════════════════════════════════════ --}}
        <div class="grid grid-cols-1 gap-3 lg:grid-cols-2">

            {{-- ── PRODUK TERJUAL (terurut) ── --}}
            <div class="rounded-xl border border-stone-200 bg-white dark:border-stone-800 dark:bg-stone-900">
                <div
                    class="flex items-center justify-between border-b border-stone-100 px-4 py-3 dark:border-stone-800">
                    <div class="flex items-center gap-2">
                        <div
                            class="bg-sage-100 dark:bg-sage-900/60 flex h-7 w-7 items-center justify-center rounded-lg">
                            <svg class="w-4 h-4 text-gray-800 dark:text-white" aria-hidden="true"
                                xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"
                                viewBox="0 0 24 24">
                                <path
                                    d="M20 7h-.7c.229-.467.349-.98.351-1.5a3.5 3.5 0 0 0-3.5-3.5c-1.717 0-3.215 1.2-4.331 2.481C10.4 2.842 8.949 2 7.5 2A3.5 3.5 0 0 0 4 5.5c.003.52.123 1.033.351 1.5H4a2 2 0 0 0-2 2v2a1 1 0 0 0 1 1h18a1 1 0 0 0 1-1V9a2 2 0 0 0-2-2Zm-9.942 0H7.5a1.5 1.5 0 0 1 0-3c.9 0 2 .754 3.092 2.122-.219.337-.392.635-.534.878Zm6.1 0h-3.742c.933-1.368 2.371-3 3.739-3a1.5 1.5 0 0 1 0 3h.003ZM13 14h-2v8h2v-8Zm-4 0H4v6a2 2 0 0 0 2 2h3v-8Zm6 0v8h3a2 2 0 0 0 2-2v-6h-5Z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xs font-semibold text-stone-800 dark:text-stone-100">
                                {{ __('Produk Terjual') }}
                            </h3>
                            <p class="text-[10px] text-stone-500 dark:text-stone-400">
                                {{ __('Terurut dari yang paling banyak') }}
                            </p>
                        </div>
                    </div>
                    <span
                        class="rounded-full bg-stone-100 px-2 py-0.5 font-mono text-[10px] font-semibold text-stone-600 dark:bg-stone-800 dark:text-stone-400">
                        {{ $topProducts->count() }}
                    </span>
                </div>

                <div class="divide-y divide-stone-100 dark:divide-stone-800/60">
                    @forelse ($topProducts as $index => $product)
                        @php
                            $maxQty = $topProducts->max('total_qty') ?: 1;
                            $percentage = ($product->total_qty / $maxQty) * 100;
                        @endphp
                        <div
                            class="flex items-center gap-3 px-4 py-2.5 transition hover:bg-stone-50 dark:hover:bg-stone-800/30">
                            {{-- Rank --}}
                            <div
                                class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-[10px] font-bold
                                {{ $index === 0 ? 'bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-400' : '' }}
                                {{ $index === 1 ? 'bg-stone-200 text-stone-700 dark:bg-stone-700 dark:text-stone-300' : '' }}
                                {{ $index === 2 ? 'bg-orange-100 text-orange-700 dark:bg-orange-950/60 dark:text-orange-400' : '' }}
                                {{ $index > 2 ? 'bg-stone-100 text-stone-500 dark:bg-stone-800 dark:text-stone-400' : '' }}">
                                {{ $index + 1 }}
                            </div>

                            {{-- Product info --}}
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center justify-between gap-2">
                                    <p class="truncate text-[11px] font-medium text-stone-800 dark:text-stone-200">
                                        {{ $product->product_name }}
                                    </p>
                                    <p
                                        class="shrink-0 font-mono text-[11px] font-semibold text-stone-700 dark:text-stone-300">
                                        {{ number_format($product->total_qty, 0, ',', '.') }} pcs
                                    </p>
                                </div>
                                <div class="mt-1 flex items-center gap-2">
                                    {{-- Progress bar --}}
                                    <div
                                        class="h-1 flex-1 overflow-hidden rounded-full bg-stone-100 dark:bg-stone-800">
                                        <div class="h-full rounded-full bg-sage-500 dark:bg-sage-400 transition-all"
                                            style="width: {{ $percentage }}%"></div>
                                    </div>
                                    <p class="shrink-0 font-mono text-[10px] text-stone-500 dark:text-stone-400">
                                        Rp {{ number_format($product->total_revenue, 0, ',', '.') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="flex flex-col items-center gap-2 px-4 py-10 text-center">
                            <svg class="h-10 w-10 text-stone-300 dark:text-stone-600" fill="none"
                                stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                            </svg>
                            <p class="text-xs font-medium text-stone-400">Belum ada produk terjual hari ini</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- ── TRANSAKSI TERBARU ── --}}
            <div class="rounded-xl border border-stone-200 bg-white dark:border-stone-800 dark:bg-stone-900">
                <div
                    class="flex items-center justify-between border-b border-stone-100 px-4 py-3 dark:border-stone-800">
                    <div class="flex items-center gap-2">
                        <div
                            class="bg-blue-100 dark:bg-blue-900/60 flex h-7 w-7 items-center justify-center rounded-lg">
                            <svg class="text-blue-700 dark:text-blue-400 h-3.5 w-3.5" fill="none"
                                stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xs font-semibold text-stone-800 dark:text-stone-100">
                                {{ __('Transaksi Terbaru') }}
                            </h3>
                            <p class="text-[10px] text-stone-500 dark:text-stone-400">
                                {{ __('5 transaksi terakhir hari ini') }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="divide-y divide-stone-100 dark:divide-stone-800/60">
                    @forelse ($recentSales as $sale)
                        <div
                            class="flex items-center gap-3 px-4 py-2.5 transition hover:bg-stone-50 dark:hover:bg-stone-800/30">
                            {{-- Avatar --}}
                            <div
                                class="bg-sage-100 dark:bg-sage-900/60 text-sage-700 dark:text-sage-400 flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-[10px] font-bold uppercase">
                                {{ mb_substr($sale->cashier_name, 0, 2) }}
                            </div>

                            {{-- Info --}}
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-1.5">
                                    <p
                                        class="truncate font-mono text-[11px] font-medium text-stone-800 dark:text-stone-200">
                                        {{ $sale->invoice_number }}
                                    </p>
                                </div>
                                <p class="truncate text-[10px] text-stone-500 dark:text-stone-400">
                                    {{ $sale->cashier_name }} · {{ $sale->store?->name ?? '—' }} ·
                                    {{ $sale->items->count() }} item
                                </p>
                            </div>

                            {{-- Total & Time --}}
                            <div class="shrink-0 text-right">
                                <p class="font-mono text-[11px] font-semibold text-sage-700 dark:text-sage-400">
                                    Rp {{ number_format($sale->total, 0, ',', '.') }}
                                </p>
                                <p class="text-[10px] text-stone-400">
                                    {{ $sale->sale_date->format('H:i') }}
                                </p>
                            </div>
                        </div>
                    @empty
                        <div class="flex flex-col items-center gap-2 px-4 py-10 text-center">
                            <svg class="h-10 w-10 text-stone-300 dark:text-stone-600" fill="none"
                                stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <p class="text-xs font-medium text-stone-400">Belum ada transaksi hari ini</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- ════════════════════════════════════════════════ --}}
        {{-- BREAKDOWN PER STORE (kalau tidak filter)       --}}
        {{-- ════════════════════════════════════════════════ --}}
        @if ($filterStore === '' && $storeBreakdown->isNotEmpty())
            <div class="rounded-xl border border-stone-200 bg-white p-4 dark:border-stone-800 dark:bg-stone-900">
                <div class="mb-3 flex items-center gap-2">
                    <div class="bg-amber-100 dark:bg-amber-900/60 flex h-7 w-7 items-center justify-center rounded-lg">
                        <svg class="text-amber-700 dark:text-amber-400 h-3.5 w-3.5" fill="none"
                            stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.65V9.35m0 0a3.001 3.001 0 003.75-.615A2.993 2.993 0 009.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 002.25 1.016c.896 0 1.7-.393 2.25-1.016a3.001 3.001 0 003.75.614m-16.5 0a3.004 3.004 0 01-.621-4.72L4.318 3.44A1.5 1.5 0 015.378 3h13.243a1.5 1.5 0 011.06.44l1.19 1.189a3 3 0 01-.621 4.72m-13.5 8.65h3.75a.75.75 0 00.75-.75V13.5a.75.75 0 00-.75-.75H6.75a.75.75 0 00-.75.75v3.75c0 .415.336.75.75.75z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-xs font-semibold text-stone-800 dark:text-stone-100">
                            {{ __('Performa per Store') }}
                        </h3>
                        <p class="text-[10px] text-stone-500 dark:text-stone-400">
                            {{ __('Pendapatan hari ini per toko') }}
                        </p>
                    </div>
                </div>

                <div class="space-y-2">
                    @foreach ($storeBreakdown as $row)
                        @php
                            $maxRevenue = $storeBreakdown->max('revenue') ?: 1;
                            $pct = ($row->revenue / $maxRevenue) * 100;
                        @endphp
                        <div class="flex items-center gap-2">
                            <div class="w-24 shrink-0 sm:w-32">
                                <p class="truncate text-[11px] font-medium text-stone-700 dark:text-stone-300">
                                    {{ $row->store?->name ?? '—' }}
                                </p>
                            </div>
                            <div class="h-2 flex-1 overflow-hidden rounded-full bg-stone-100 dark:bg-stone-800">
                                <div class="h-full rounded-full bg-sage-500 dark:bg-sage-400"
                                    style="width: {{ $pct }}%"></div>
                            </div>
                            <div class="w-24 shrink-0 text-right sm:w-32">
                                <p class="font-mono text-[10px] font-semibold text-stone-700 dark:text-stone-300">
                                    Rp {{ number_format($row->revenue, 0, ',', '.') }}
                                </p>
                                <p class="text-[9px] text-stone-400">{{ $row->transactions }} transaksi</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

    </div>
</div>
