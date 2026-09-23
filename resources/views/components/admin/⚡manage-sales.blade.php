<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use App\Models\Sale;
use App\Models\Store;

new #[Title('Manage Sales')] class extends Component {
    public $search;
    public $filterStore = '';
    public $filterStatus = '';
    public $dateFrom = '';
    public $dateTo = '';

    public function with()
    {
        // Query dasar dengan filter (untuk data yang ditampilkan)
        $sales = Sale::query()
            ->with(['store', 'items'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('invoice_number', 'like', '%' . $this->search . '%')->orWhere('cashier_name', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->filterStore !== '', function ($query) {
                $query->where('store_id', $this->filterStore);
            })
            ->when($this->filterStatus !== '', function ($query) {
                $query->where('status', $this->filterStatus);
            })
            ->when($this->dateFrom, function ($query) {
                $query->whereDate('sale_date', '>=', $this->dateFrom);
            })
            ->when($this->dateTo, function ($query) {
                $query->whereDate('sale_date', '<=', $this->dateTo);
            })
            ->orderByDesc('sale_date')
            ->take(15)
            ->get(); // ← get, bukan paginate

        // Summary dihitung dari SELURUH data (tanpa filter & tanpa take)
        $summary = [
            'total_transactions' => Sale::count(),
            'total_revenue' => Sale::where('status', 'completed')->sum('total'),
            'today_transactions' => Sale::whereDate('sale_date', today())->count(),
            'today_revenue' => Sale::whereDate('sale_date', today())->where('status', 'completed')->sum('total'),
        ];

        return [
            'sales' => $sales,
            'stores' => Store::orderBy('name')->get(),
            'summary' => $summary,
            'isFiltered' => $this->search || $this->filterStore || $this->filterStatus || $this->dateFrom || $this->dateTo,
        ];
    }
};
?>

<div>
    <x-page-header title="Manage Sales" leading="Riwayat semua transaksi penjualan" />

    <div class="mx-auto mt-2 max-w-7xl space-y-2">

        {{-- SUMMARY CARDS --}}
        <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
            {{-- Total Transaksi --}}
            <div class="rounded-xl border border-stone-200 bg-white p-3 dark:border-stone-800 dark:bg-stone-900">
                <p class="text-[10px] font-medium uppercase tracking-wider text-stone-500 dark:text-stone-400">
                    {{ __('Total Transaksi') }}
                </p>
                <p class="mt-0.5 font-mono text-lg font-semibold text-stone-800 dark:text-stone-100">
                    {{ number_format($summary['total_transactions'], 0, ',', '.') }}
                </p>
            </div>

            {{-- Total Revenue --}}
            <div class="rounded-xl border border-stone-200 bg-white p-3 dark:border-stone-800 dark:bg-stone-900">
                <p class="text-[10px] font-medium uppercase tracking-wider text-stone-500 dark:text-stone-400">
                    {{ __('Total Pendapatan') }}
                </p>
                <p class="mt-0.5 font-mono text-lg font-semibold text-sage-700 dark:text-sage-400">
                    Rp {{ number_format($summary['total_revenue'], 0, ',', '.') }}
                </p>
            </div>

            {{-- Transaksi Hari Ini --}}
            <div class="rounded-xl border border-stone-200 bg-white p-3 dark:border-stone-800 dark:bg-stone-900">
                <p class="text-[10px] font-medium uppercase tracking-wider text-stone-500 dark:text-stone-400">
                    {{ __('Transaksi Hari Ini') }}
                </p>
                <p class="mt-0.5 font-mono text-lg font-semibold text-blue-600 dark:text-blue-400">
                    {{ number_format($summary['today_transactions'], 0, ',', '.') }}
                </p>
            </div>

            {{-- Pendapatan Hari Ini --}}
            <div class="rounded-xl border border-stone-200 bg-white p-3 dark:border-stone-800 dark:bg-stone-900">
                <p class="text-[10px] font-medium uppercase tracking-wider text-stone-500 dark:text-stone-400">
                    {{ __('Pendapatan Hari Ini') }}
                </p>
                <p class="mt-0.5 font-mono text-lg font-semibold text-blue-600 dark:text-blue-400">
                    Rp {{ number_format($summary['today_revenue'], 0, ',', '.') }}
                </p>
            </div>
        </div>

        {{-- FILTER BAR --}}
        <div class="rounded-xl border border-stone-200 bg-white px-3 py-3 dark:border-stone-800 dark:bg-stone-900">
            <div class="flex flex-col gap-2 sm:flex-row sm:flex-wrap">

                {{-- Search --}}
                <div class="relative flex-1 min-w-[200px]">
                    <svg class="pointer-events-none absolute left-2.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-stone-400 dark:text-stone-500"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z" />
                    </svg>
                    <input wire:model.live.debounce.300ms="search" type="text"
                        placeholder="Cari invoice atau nama kasir…"
                        class="focus:ring-sage-500 w-full rounded-lg border border-stone-200 bg-stone-50 py-1.5 pl-8 pr-3 text-xs text-stone-800 transition placeholder:text-stone-400 focus:border-transparent focus:outline-none focus:ring-1 dark:border-stone-700 dark:bg-stone-800 dark:text-stone-100" />
                </div>

                {{-- Filter Store --}}
                <select wire:model.live="filterStore"
                    class="focus:ring-sage-500 rounded-lg border border-stone-200 bg-stone-50 px-2.5 py-1.5 text-xs text-stone-700 transition focus:outline-none focus:ring-1 dark:border-stone-700 dark:bg-stone-800 dark:text-stone-300">
                    <option value="">Semua Store</option>
                    @foreach ($stores as $store)
                        <option value="{{ $store->id }}">{{ $store->name }}</option>
                    @endforeach
                </select>

                {{-- Filter Status --}}
                <select wire:model.live="filterStatus"
                    class="focus:ring-sage-500 rounded-lg border border-stone-200 bg-stone-50 px-2.5 py-1.5 text-xs text-stone-700 transition focus:outline-none focus:ring-1 dark:border-stone-700 dark:bg-stone-800 dark:text-stone-300">
                    <option value="">Semua Status</option>
                    <option value="completed">Completed</option>
                    <option value="void">Void</option>
                    <option value="refunded">Refunded</option>
                </select>

                {{-- Date From --}}
                <input wire:model.live="dateFrom" type="date"
                    class="focus:ring-sage-500 rounded-lg border border-stone-200 bg-stone-50 px-2.5 py-1.5 text-xs text-stone-700 transition focus:outline-none focus:ring-1 dark:border-stone-700 dark:bg-stone-800 dark:text-stone-300" />

                {{-- Date To --}}
                <input wire:model.live="dateTo" type="date"
                    class="focus:ring-sage-500 rounded-lg border border-stone-200 bg-stone-50 px-2.5 py-1.5 text-xs text-stone-700 transition focus:outline-none focus:ring-1 dark:border-stone-700 dark:bg-stone-800 dark:text-stone-300" />

                {{-- Reset --}}
                @if ($search || $filterStore || $filterStatus || $dateFrom || $dateTo)
                    <button
                        wire:click="$set('search', ''); $set('filterStore', ''); $set('filterStatus', ''); $set('dateFrom', ''); $set('dateTo', '')"
                        class="rounded-lg border border-stone-200 px-2.5 py-1.5 text-xs font-medium text-stone-600 transition hover:bg-stone-50 dark:border-stone-700 dark:text-stone-400 dark:hover:bg-stone-800">
                        {{ __('Reset') }}
                    </button>
                @endif
            </div>
        </div>

        {{-- TABLE CARD --}}
        <div
            class="overflow-hidden rounded-xl border border-stone-200 bg-white px-4 dark:border-stone-800 dark:bg-stone-900">

            {{-- Table meta --}}
            <div class="flex items-center justify-between border-b border-stone-100 py-2 dark:border-stone-800">
                <span class="font-mono text-[10px] uppercase tracking-wider text-stone-400 dark:text-stone-500">
                    {{ __('Menampilkan') }} {{ $sales->count() }} {{ __('transaksi terbaru') }}
                    @if ($isFiltered)
                        · <span class="text-sage-600 dark:text-sage-400">{{ __('(terfilter)') }}</span>
                    @endif
                </span>
                <div wire:loading class="text-sage-600 dark:text-sage-400 flex items-center gap-1 text-[11px]">
                    <svg class="h-3 w-3 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z" />
                    </svg>
                    {{ __('Memuat…') }}
                </div>
            </div>

            {{-- Table --}}
            <div class="overflow-x-auto rounded-xl border bg-white shadow-sm dark:border-stone-800 dark:bg-stone-900">
                <table class="w-full border-collapse text-left text-[11px]">
                    <thead>
                        <tr
                            class="border-b border-stone-200 bg-stone-50 font-semibold uppercase tracking-wider text-stone-500 dark:border-stone-800 dark:bg-stone-800/50 dark:text-stone-400">
                            <th class="w-6 px-2.5 py-1.5 text-center">#</th>
                            <th class="px-2.5 py-1.5">{{ __('Invoice') }}</th>
                            <th class="hidden px-2.5 py-1.5 sm:table-cell">{{ __('Tanggal') }}</th>
                            <th class="hidden px-2.5 py-1.5 md:table-cell">{{ __('Kasir') }}</th>
                            <th class="hidden px-2.5 py-1.5 lg:table-cell">{{ __('Store') }}</th>
                            <th class="hidden w-16 px-2.5 py-1.5 text-center md:table-cell">{{ __('Item') }}</th>
                            <th class="px-2.5 py-1.5 text-right">{{ __('Total') }}</th>
                            <th class="hidden w-20 px-2.5 py-1.5 text-center sm:table-cell">{{ __('Status') }}</th>
                            <th class="w-16 px-2.5 py-1.5 text-right">{{ __('Aksi') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100 dark:divide-stone-800/60">
                        @forelse ($sales as $sale)
                            <tr class="group transition-colors hover:bg-stone-50 dark:hover:bg-stone-800/30">

                                {{-- No --}}
                                <td class="px-2.5 py-1.5 text-center font-mono text-stone-400 dark:text-stone-600">
                                    {{ $loop->iteration }}
                                </td>

                                {{-- Invoice --}}
                                <td class="px-2.5 py-1.5 font-mono font-medium text-stone-800 dark:text-stone-200">
                                    {{ $sale->invoice_number }}
                                </td>

                                {{-- Tanggal --}}
                                <td class="hidden px-2.5 py-1.5 text-stone-500 sm:table-cell dark:text-stone-400">
                                    {{ $sale->sale_date->format('d M Y, H:i') }}
                                </td>

                                {{-- Kasir --}}
                                <td class="hidden px-2.5 py-1.5 text-stone-500 md:table-cell dark:text-stone-400">
                                    {{ $sale->cashier_name }}
                                </td>

                                {{-- Store --}}
                                <td class="hidden px-2.5 py-1.5 text-stone-500 lg:table-cell dark:text-stone-400">
                                    {{ $sale->store?->name ?? '—' }}
                                </td>

                                {{-- Item Count --}}
                                <td class="hidden px-2.5 py-1.5 text-center md:table-cell">
                                    <span
                                        class="inline-flex items-center rounded-full bg-stone-100 px-2 py-0.5 text-[10px] font-semibold text-stone-600 dark:bg-stone-800 dark:text-stone-400">
                                        {{ $sale->items->count() }}
                                    </span>
                                </td>

                                {{-- Total --}}
                                <td
                                    class="px-2.5 py-1.5 text-right font-mono font-medium text-sage-700 dark:text-sage-400">
                                    Rp {{ number_format($sale->total, 0, ',', '.') }}
                                </td>

                                {{-- Status --}}
                                <td class="hidden px-2.5 py-1.5 text-center sm:table-cell">
                                    @php
                                        $statusClasses =
                                            [
                                                'completed' =>
                                                    'bg-green-50 text-green-700 dark:bg-green-950/40 dark:text-green-400',
                                                'void' => 'bg-red-50 text-red-700 dark:bg-red-950/40 dark:text-red-400',
                                                'refunded' =>
                                                    'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400',
                                            ][$sale->status] ??
                                            'bg-stone-100 text-stone-600 dark:bg-stone-800 dark:text-stone-400';
                                    @endphp
                                    <span
                                        class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $statusClasses }}">
                                        {{ ucfirst($sale->status) }}
                                    </span>
                                </td>

                                {{-- Aksi --}}
                                <td class="px-2.5 py-1.5 text-right">
                                    <div class="flex items-center justify-end" x-data="{ open: false }">
                                        <div class="relative inline-block text-left">

                                            {{-- Tombol Titik Tiga --}}
                                            <button @click="open = !open" @click.outside="open = false"
                                                class="cursor-pointer rounded-md p-1 text-stone-400 transition-colors hover:bg-stone-100 hover:text-stone-700 focus:outline-none dark:hover:bg-stone-800 dark:hover:text-stone-200"
                                                title="Menu Aksi">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor"
                                                    stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M12 6.75a.75.75 0 110-1.5.75.75 0 010 1.5zM12 12.75a.75.75 0 110-1.5.75.75 0 010 1.5zM12 18.75a.75.75 0 110-1.5.75.75 0 010 1.5z" />
                                                </svg>
                                            </button>

                                            {{-- Dropdown --}}
                                            <div x-show="open" x-transition
                                                class="absolute right-0 z-30 mt-1 w-36 origin-top-right rounded-md border border-stone-200 bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none dark:border-stone-800 dark:bg-stone-900"
                                                style="display: none;">
                                                <div class="space-y-0.5 p-1">
                                                    {{-- Lihat Detail --}}
                                                    <button x-data
                                                        x-on:click="$dispatch('open-detail-sale-modal', { saleId: {{ $sale->id }} })"
                                                        @click="open = false"
                                                        class="text-sage-600 dark:text-sage-400 hover:bg-sage-50 dark:hover:bg-sage-950/30 flex w-full cursor-pointer items-center gap-2 rounded px-2.5 py-1.5 text-left text-xs transition-colors">
                                                        <svg class="text-sage-500 h-3.5 w-3.5" fill="none"
                                                            stroke="currentColor" stroke-width="2"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        </svg>
                                                        Lihat Detail
                                                    </button>

                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-2.5 py-10 text-center">
                                    <div class="flex flex-col items-center gap-1.5 text-stone-400">
                                        <svg class="h-8 w-8 text-stone-300 dark:text-stone-600" fill="none"
                                            stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        <p class="text-xs font-medium">{{ __('Tidak ada transaksi ditemukan') }}</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
    <livewire:admin.sales.detail-sale />
</div>
