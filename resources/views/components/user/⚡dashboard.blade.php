<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use App\Models\Sale;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

new #[Title('Dashboard Kasir')] class extends Component {
    public function with()
    {
        $user = Auth::user();
        $storeId = $user->store_id;
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();

        /* ══════════════════════════════════════════════
           Base query — LOCK ke store kasir
           ══════════════════════════════════════════════ */
        $todayQuery = Sale::query()->where('status', 'completed')->where('store_id', $storeId)->whereDate('sale_date', $today);

        /* ── METRIC STORE (semua kasir di toko ini) ── */
        $storeRevenue = (clone $todayQuery)->sum('total');
        $storeTransactions = (clone $todayQuery)->count();
        $storeItemsSold = (int) DB::table('sale_items')->join('sales', 'sales.id', '=', 'sale_items.sale_id')->where('sales.status', 'completed')->where('sales.store_id', $storeId)->whereDate('sales.sale_date', $today)->sum('sale_items.quantity');

        /* ── METRIC PERSONAL (kasir ini saja) ── */
        $myQuery = (clone $todayQuery)->where('cashier_id', $user->id);
        $myRevenue = (clone $myQuery)->sum('total');
        $myTransactions = (clone $myQuery)->count();
        $myItemsSold = (int) DB::table('sale_items')->join('sales', 'sales.id', '=', 'sale_items.sale_id')->where('sales.status', 'completed')->where('sales.store_id', $storeId)->where('sales.cashier_id', $user->id)->whereDate('sales.sale_date', $today)->sum('sale_items.quantity');

        /* ── PENDAPATAN KEMARIN (personal, untuk growth) ── */
        $yesterdayMyRevenue = Sale::query()->where('status', 'completed')->where('store_id', $storeId)->where('cashier_id', $user->id)->whereDate('sale_date', $yesterday)->sum('total');

        $myRevenueGrowth = $yesterdayMyRevenue > 0 ? (($myRevenue - $yesterdayMyRevenue) / $yesterdayMyRevenue) * 100 : ($myRevenue > 0 ? 100 : 0);

        /* ── TOP PRODUK HARI INI (dari toko, karena kasir jual produk yang sama) ── */
        $topProducts = DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->where('sales.status', 'completed')
            ->where('sales.store_id', $storeId)
            ->whereDate('sales.sale_date', $today)
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

        /* ── TRANSAKSI TERBARU SAYA (kasir ini) ── */
        $myRecentSales = (clone $myQuery)
            ->with(['store', 'items'])
            ->orderByDesc('sale_date')
            ->take(5)
            ->get();

        /* ── PRODUK UNIK (produk yang saya jual hari ini) ── */
        $myUniqueProducts = DB::table('sale_items')->join('sales', 'sales.id', '=', 'sale_items.sale_id')->where('sales.status', 'completed')->where('sales.store_id', $storeId)->where('sales.cashier_id', $user->id)->whereDate('sales.sale_date', $today)->distinct('sale_items.product_id')->count('sale_items.product_id');

        /* ── PRODUK YANG DI-ASSIGN KE TOKO INI ── */
        $storeProducts = \App\Models\Product::query()
            ->whereHas('stores', fn($q) => $q->where('stores.id', $storeId))
            ->with(['stores' => fn($q) => $q->where('stores.id', $storeId)])
            ->orderBy('name')
            ->get()
            ->map(function ($product) {
                $pivot = $product->stores->first()?->pivot;
                return (object) [
                    'id' => $product->id,
                    'name' => $product->name,
                    'code' => $product->code,
                    'image_url' => $product->image_url ?? null,
                    'emoji' => $product->emoji ?? '📦',
                    'is_active' => (bool) $product->is_active,
                    'is_available' => (bool) ($pivot?->is_available ?? false),
                    'price' => (float) ($pivot?->price ?? $product->price),
                    'discount_price' => $product->discount_price,
                    'minimal_discount' => $product->minimal_discount,
                ];
            });

        /* ── STATISTIK PRODUK ── */
        $productStats = [
            'total' => $storeProducts->count(),
            'available' => $storeProducts->where('is_available', true)->where('is_active', true)->count(),
            'promo' => $storeProducts->where('is_active', true)->where('is_available', true)->whereNotNull('discount_price')->whereNotNull('minimal_discount')->count(),
        ];

        return [
            'user' => $user,
            'store' => $user->store,
            'metrics' => [
                /* Personal */
                'myRevenue' => $myRevenue,
                'myTransactions' => $myTransactions,
                'myItemsSold' => $myItemsSold,
                'myUniqueProducts' => $myUniqueProducts,
                'yesterdayMyRevenue' => $yesterdayMyRevenue,
                'myRevenueGrowth' => $myRevenueGrowth,
                'myAvgTransaction' => $myTransactions > 0 ? $myRevenue / $myTransactions : 0,
                /* Store (perbandingan) */
                'storeRevenue' => $storeRevenue,
                'storeTransactions' => $storeTransactions,
                'storeItemsSold' => $storeItemsSold,
            ],
            'topProducts' => $topProducts,
            'myRecentSales' => $myRecentSales,
            'storeProducts' => $storeProducts,
            'productStats' => $productStats,
        ];
    }
};
?>

<div>
    <x-page-header title="Dashboard" leading="Ringkasan penjualan Anda hari ini" :time="true" />

    <div class="mx-auto mt-2 max-w-7xl space-y-3">

        {{-- ── INFO STORE ── --}}
        <div class="rounded-xl border border-stone-200 bg-white px-3 py-3 dark:border-stone-800 dark:bg-stone-900">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-3">
                    <div
                        class="bg-sage-100 dark:bg-sage-900/60 text-sage-700 dark:text-sage-400 flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-xs font-bold uppercase">
                        {{ mb_substr($store->name ?? 'T', 0, 2) }}
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-stone-800 dark:text-stone-100">
                            {{ $store->name ?? 'Toko' }}
                        </p>
                        <p class="text-[10px] text-stone-500 dark:text-stone-400">
                            {{ $store->code ?? '—' }} · {{ $store->location ?? '—' }}
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <span
                        class="rounded-full bg-stone-100 px-2.5 py-1 text-[10px] font-medium text-stone-600 dark:bg-stone-800 dark:text-stone-400">
                        {{ now()->translatedFormat('l, d F Y') }}
                    </span>
                </div>
            </div>
        </div>

        {{-- ════════════════════════════════════════════════ --}}
        {{-- METRICS CARDS                                  --}}
        {{-- ════════════════════════════════════════════════ --}}
        <div class="grid grid-cols-2 gap-2 lg:grid-cols-4">

            {{-- Pendapatan Saya --}}
            <div class="rounded-xl border border-stone-200 bg-white p-3 dark:border-stone-800 dark:bg-stone-900">
                <div class="flex items-start justify-between">
                    <div class="min-w-0 flex-1">
                        <p class="text-[10px] font-medium uppercase tracking-wider text-stone-500 dark:text-stone-400">
                            {{ __('Pendapatan Saya') }}
                        </p>
                        <p class="mt-1 truncate font-mono text-lg font-semibold text-sage-700 dark:text-sage-400">
                            Rp {{ number_format($metrics['myRevenue'], 0, ',', '.') }}
                        </p>
                        @if ($metrics['myRevenue'] > 0 || $metrics['yesterdayMyRevenue'] > 0)
                            <div class="mt-1 flex items-center gap-1">
                                @if ($metrics['myRevenueGrowth'] >= 0)
                                    <svg class="h-3 w-3 text-green-600 dark:text-green-400" fill="none"
                                        stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M4.5 15.75l7.5-7.5 7.5 7.5" />
                                    </svg>
                                    <span class="text-[10px] font-semibold text-green-600 dark:text-green-400">
                                        +{{ number_format($metrics['myRevenueGrowth'], 1) }}%
                                    </span>
                                @else
                                    <svg class="h-3 w-3 text-red-600 dark:text-red-400" fill="none"
                                        stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                    </svg>
                                    <span class="text-[10px] font-semibold text-red-600 dark:text-red-400">
                                        {{ number_format($metrics['myRevenueGrowth'], 1) }}%
                                    </span>
                                @endif
                                <span class="text-[10px] text-stone-400 dark:text-stone-500">vs kemarin</span>
                            </div>
                        @else
                            <p class="mt-1 text-[10px] text-stone-400">Belum ada transaksi</p>
                        @endif
                    </div>
                    <div
                        class="bg-sage-100 dark:bg-sage-900/60 flex h-7 w-7 shrink-0 items-center justify-center rounded-lg">
                        <svg class="text-sage-700 dark:text-sage-400 h-3.5 w-3.5" fill="none" stroke="currentColor"
                            stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Transaksi Saya --}}
            <div class="rounded-xl border border-stone-200 bg-white p-3 dark:border-stone-800 dark:bg-stone-900">
                <div class="flex items-start justify-between">
                    <div class="min-w-0 flex-1">
                        <p class="text-[10px] font-medium uppercase tracking-wider text-stone-500 dark:text-stone-400">
                            {{ __('Transaksi Saya') }}
                        </p>
                        <p class="mt-1 font-mono text-lg font-semibold text-stone-800 dark:text-stone-100">
                            {{ number_format($metrics['myTransactions'], 0, ',', '.') }}
                        </p>
                        <p class="mt-1 text-[10px] text-stone-400">
                            Rata-rata Rp {{ number_format($metrics['myAvgTransaction'], 0, ',', '.') }}
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

            {{-- Item Terjual Saya --}}
            <div class="rounded-xl border border-stone-200 bg-white p-3 dark:border-stone-800 dark:bg-stone-900">
                <div class="flex items-start justify-between">
                    <div class="min-w-0 flex-1">
                        <p class="text-[10px] font-medium uppercase tracking-wider text-stone-500 dark:text-stone-400">
                            {{ __('Item Terjual') }}
                        </p>
                        <p class="mt-1 font-mono text-lg font-semibold text-stone-800 dark:text-stone-100">
                            {{ number_format($metrics['myItemsSold'], 0, ',', '.') }}
                        </p>
                        <p class="mt-1 text-[10px] text-stone-400">pcs dari transaksi Anda</p>
                    </div>
                    <div
                        class="bg-purple-100 dark:bg-purple-900/60 flex h-7 w-7 shrink-0 items-center justify-center rounded-lg">
                        <svg class="text-purple-700 dark:text-purple-400 h-3.5 w-3.5" fill="none"
                            stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
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
                            {{ $metrics['myUniqueProducts'] }}
                        </p>
                        <p class="mt-1 text-[10px] text-stone-400">produk berbeda hari ini</p>
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
        {{-- STORE SUMMARY (perbandingan dengan toko)       --}}
        {{-- ════════════════════════════════════════════════ --}}
        <div class="rounded-xl border border-stone-200 bg-white p-4 dark:border-stone-800 dark:bg-stone-900">
            <div class="mb-3 flex items-center gap-2">
                <div class="bg-stone-100 dark:bg-stone-800 flex h-7 w-7 items-center justify-center rounded-lg">
                    <svg class="text-stone-700 dark:text-stone-400 h-3.5 w-3.5" fill="none" stroke="currentColor"
                        stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.65V9.35m0 0a3.001 3.001 0 003.75-.615A2.993 2.993 0 009.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 002.25 1.016c.896 0 1.7-.393 2.25-1.016a3.001 3.001 0 003.75.614m-16.5 0a3.004 3.004 0 01-.621-4.72L4.318 3.44A1.5 1.5 0 015.378 3h13.243a1.5 1.5 0 011.06.44l1.19 1.189a3 3 0 01-.621 4.72m-13.5 8.65h3.75a.75.75 0 00.75-.75V13.5a.75.75 0 00-.75-.75H6.75a.75.75 0 00-.75.75v3.75c0 .415.336.75.75.75z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-xs font-semibold text-stone-800 dark:text-stone-100">
                        {{ __('Performa Toko Hari Ini') }}
                    </h3>
                    <p class="text-[10px] text-stone-500 dark:text-stone-400">
                        {{ __('Total seluruh kasir di toko ini') }}
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                <div
                    class="rounded-lg border border-stone-200 bg-stone-50 p-3 dark:border-stone-700 dark:bg-stone-800/50">
                    <p class="text-[10px] uppercase tracking-wider text-stone-500 dark:text-stone-400">Pendapatan Toko
                    </p>
                    <p class="mt-1 font-mono text-base font-semibold text-stone-800 dark:text-stone-100">
                        Rp {{ number_format($metrics['storeRevenue'], 0, ',', '.') }}
                    </p>
                </div>
                <div
                    class="rounded-lg border border-stone-200 bg-stone-50 p-3 dark:border-stone-700 dark:bg-stone-800/50">
                    <p class="text-[10px] uppercase tracking-wider text-stone-500 dark:text-stone-400">Total Transaksi
                    </p>
                    <p class="mt-1 font-mono text-base font-semibold text-stone-800 dark:text-stone-100">
                        {{ number_format($metrics['storeTransactions'], 0, ',', '.') }}
                    </p>
                </div>
                <div
                    class="rounded-lg border border-stone-200 bg-stone-50 p-3 dark:border-stone-700 dark:bg-stone-800/50">
                    <p class="text-[10px] uppercase tracking-wider text-stone-500 dark:text-stone-400">Item Terjual</p>
                    <p class="mt-1 font-mono text-base font-semibold text-stone-800 dark:text-stone-100">
                        {{ number_format($metrics['storeItemsSold'], 0, ',', '.') }} pcs
                    </p>
                </div>
            </div>
        </div>

        {{-- ════════════════════════════════════════════════ --}}
        {{-- PRODUK TERJUAL & TRANSAKSI SAYA               --}}
        {{-- ════════════════════════════════════════════════ --}}
        <div class="grid grid-cols-1 gap-3 lg:grid-cols-2">

            {{-- Produk Terjual --}}
            <div class="rounded-xl border border-stone-200 bg-white dark:border-stone-800 dark:bg-stone-900">
                <div
                    class="flex items-center justify-between border-b border-stone-100 px-4 py-3 dark:border-stone-800">
                    <div class="flex items-center gap-2">
                        <div
                            class="bg-sage-100 dark:bg-sage-900/60 flex h-7 w-7 items-center justify-center rounded-lg">
                            <svg class="text-sage-700 dark:text-sage-400 h-3.5 w-3.5" fill="none"
                                stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M16.5 18.75h-9m9 0a3 3 0 013 3h-15a3 3 0 013-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 01-.982-3.172M9.497 14.25a7.454 7.454 0 00.981-3.172" />
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
                            <div
                                class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-[10px] font-bold
                                {{ $index === 0 ? 'bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-400' : '' }}
                                {{ $index === 1 ? 'bg-stone-200 text-stone-700 dark:bg-stone-700 dark:text-stone-300' : '' }}
                                {{ $index === 2 ? 'bg-orange-100 text-orange-700 dark:bg-orange-950/60 dark:text-orange-400' : '' }}
                                {{ $index > 2 ? 'bg-stone-100 text-stone-500 dark:bg-stone-800 dark:text-stone-400' : '' }}">
                                {{ $index + 1 }}
                            </div>

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

            {{-- Transaksi Terbaru Saya --}}
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
                                {{ __('Transaksi Saya') }}
                            </h3>
                            <p class="text-[10px] text-stone-500 dark:text-stone-400">
                                {{ __('5 transaksi terakhir Anda hari ini') }}
                            </p>
                        </div>
                    </div>
                    <span
                        class="rounded-full bg-blue-50 px-2 py-0.5 font-mono text-[10px] font-semibold text-blue-600 dark:bg-blue-950/40 dark:text-blue-400">
                        {{ $metrics['myTransactions'] }}
                    </span>
                </div>

                <div class="divide-y divide-stone-100 dark:divide-stone-800/60">
                    @forelse ($myRecentSales as $sale)
                        <div
                            class="flex items-center gap-3 px-4 py-2.5 transition hover:bg-stone-50 dark:hover:bg-stone-800/30">
                            <div
                                class="bg-sage-100 dark:bg-sage-900/60 text-sage-700 dark:text-sage-400 flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-[10px] font-bold uppercase">
                                {{ mb_substr($sale->cashier_name, 0, 2) }}
                            </div>

                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-1.5">
                                    <p
                                        class="truncate font-mono text-[11px] font-medium text-stone-800 dark:text-stone-200">
                                        {{ $sale->invoice_number }}
                                    </p>
                                </div>
                                <p class="truncate text-[10px] text-stone-500 dark:text-stone-400">
                                    {{ $sale->items->count() }} item · {{ $sale->items->sum('quantity') }} pcs
                                </p>
                            </div>

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
        {{-- PRODUK DI TOKO ANDA                            --}}
        {{-- ════════════════════════════════════════════════ --}}
        <div class="rounded-xl border border-stone-200 bg-white dark:border-stone-800 dark:bg-stone-900">

            {{-- Header --}}
            <div class="flex items-center justify-between border-b border-stone-100 px-4 py-3 dark:border-stone-800">
                <div class="flex items-center gap-2">
                    <div class="bg-sage-100 dark:bg-sage-900/60 flex h-7 w-7 items-center justify-center rounded-lg">
                        <svg class="text-sage-700 dark:text-sage-400 h-3.5 w-3.5" fill="none"
                            stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.65V9.35m0 0a3.001 3.001 0 003.75-.615A2.993 2.993 0 009.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 002.25 1.016c.896 0 1.7-.393 2.25-1.016a3.001 3.001 0 003.75.614m-16.5 0a3.004 3.004 0 01-.621-4.72L4.318 3.44A1.5 1.5 0 015.378 3h13.243a1.5 1.5 0 011.06.44l1.19 1.189a3 3 0 01-.621 4.72m-13.5 8.65h3.75a.75.75 0 00.75-.75V13.5a.75.75 0 00-.75-.75H6.75a.75.75 0 00-.75.75v3.75c0 .415.336.75.75.75z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-xs font-semibold text-stone-800 dark:text-stone-100">
                            {{ __('Produk di Toko Anda') }}
                        </h3>
                        <p class="text-[10px] text-stone-500 dark:text-stone-400">
                            {{ __('Daftar produk yang bisa Anda jual') }}
                        </p>
                    </div>
                </div>

                {{-- Stats Pills --}}
                <div class="flex items-center gap-1.5">
                    <span
                        class="rounded-full bg-stone-100 px-2 py-0.5 font-mono text-[10px] font-semibold text-stone-600 dark:bg-stone-800 dark:text-stone-400">
                        {{ $productStats['total'] }} total
                    </span>
                    <span
                        class="rounded-full bg-green-50 px-2 py-0.5 font-mono text-[10px] font-semibold text-green-700 dark:bg-green-950/40 dark:text-green-400">
                        {{ $productStats['available'] }} aktif
                    </span>
                    @if ($productStats['promo'] > 0)
                        <span
                            class="rounded-full bg-red-50 px-2 py-0.5 font-mono text-[10px] font-semibold text-red-700 dark:bg-red-950/40 dark:text-red-400">
                            {{ $productStats['promo'] }} promo
                        </span>
                    @endif
                </div>
            </div>

            {{-- Grid Produk --}}
            @if ($storeProducts->isEmpty())
                <div class="flex flex-col items-center gap-2 px-4 py-12 text-center">
                    <svg class="h-10 w-10 text-stone-300 dark:text-stone-600" fill="none" stroke="currentColor"
                        stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                    </svg>
                    <p class="text-xs font-medium text-stone-400">Belum ada produk yang di-assign ke toko Anda</p>
                    <p class="text-[10px] text-stone-400">Hubungi admin untuk menambahkan produk</p>
                </div>
            @else
                <div class="grid grid-cols-2 gap-2 p-3 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
                    @foreach ($storeProducts as $product)
                        @php
                            $hasPromo = $product->discount_price && $product->minimal_discount;
                            $displayPrice = $hasPromo ? $product->discount_price : $product->price;
                            $isDisabled = !$product->is_active || !$product->is_available;
                        @endphp

                        <div
                            class="relative overflow-hidden rounded-lg border bg-white transition dark:bg-stone-900
                        {{ $isDisabled ? 'border-stone-200 opacity-60 dark:border-stone-800' : 'border-stone-200 dark:border-stone-700' }}">

                            {{-- Image --}}
                            <div
                                class="relative flex h-20 items-center justify-center overflow-hidden bg-stone-100 dark:bg-stone-800">
                                @if ($product->image_url)
                                    <img src="{{ \Illuminate\Support\Facades\Storage::url($product->image_url) }}"
                                        alt="{{ $product->name }}" class="h-full w-full object-cover"
                                        onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';" />
                                    <div class="hidden h-full w-full items-center justify-center text-3xl">
                                        {{ $product->emoji }}
                                    </div>
                                @else
                                    <span class="text-3xl">{{ $product->emoji }}</span>
                                @endif

                                {{-- Code Badge --}}
                                <span
                                    class="absolute left-1 top-1 rounded-full bg-white/95 px-1.5 py-0.5 font-mono text-[9px] font-bold text-stone-700 dark:bg-stone-900/95 dark:text-stone-300">
                                    {{ $product->code }}
                                </span>

                                {{-- Status Badges --}}
                                @if (!$product->is_active)
                                    <span
                                        class="absolute right-1 top-1 rounded-full bg-red-500/90 px-1.5 py-0.5 text-[8px] font-bold uppercase text-white">
                                        Nonaktif
                                    </span>
                                @elseif (!$product->is_available)
                                    <span
                                        class="absolute right-1 top-1 rounded-full bg-amber-500/90 px-1.5 py-0.5 text-[8px] font-bold uppercase text-white">
                                        Habis
                                    </span>
                                @endif
                            </div>

                            {{-- Info --}}
                            <div class="p-2">
                                <p class="truncate text-[11px] font-semibold text-stone-800 dark:text-stone-200"
                                    title="{{ $product->name }}">
                                    {{ $product->name }}
                                </p>

                                <div class="mt-1 space-y-0.5">
                                    @if ($hasPromo)
                                        <p
                                            class="font-mono text-[9px] text-stone-400 line-through dark:text-stone-500">
                                            Rp {{ number_format($product->price, 0, ',', '.') }}
                                        </p>
                                    @endif
                                    <p class="font-mono text-[12px] font-bold text-sage-700 dark:text-sage-400">
                                        Rp {{ number_format($displayPrice, 0, ',', '.') }}
                                    </p>
                                </div>

                                @if ($hasPromo)
                                    <div class="mt-1 rounded bg-red-50 px-1 py-0.5 dark:bg-red-950/40">
                                        <p class="text-[9px] font-semibold text-red-600 dark:text-red-400">
                                            ≥{{ $product->minimal_discount }} pcs
                                        </p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
