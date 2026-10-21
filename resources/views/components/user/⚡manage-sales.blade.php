<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\On;
use App\Models\Sale;
use Illuminate\Support\Facades\Auth;

new #[Title('Sale History')] class extends Component {
    public $search;
    public $filterStatus = '';
    public $dateFrom = '';
    public $dateTo = '';

    // === Bulk delete state ===
    public array $selectedSales = [];
    public bool $selectAll = false;

    /**
     * Query dasar — LOCK ke kasir yang login.
     */
    protected function buildSalesQuery()
    {
        $user = Auth::user();

        return Sale::query()
            ->with(['store', 'items'])
            ->where('cashier_id', $user->id)
            ->when($this->search, fn ($q) => $q->where('invoice_number', 'like', '%' . $this->search . '%'))
            ->when($this->filterStatus !== '', fn ($q) => $q->where('status', $this->filterStatus))
            ->when($this->dateFrom, fn ($q) => $q->whereDate('sale_date', '>=', $this->dateFrom))
            ->when($this->dateTo, fn ($q) => $q->whereDate('sale_date', '<=', $this->dateTo))
            ->orderByDesc('sale_date');
    }

    public function updatedSelectAll($value): void
    {
        $this->selectedSales = $value
            ? $this->buildSalesQuery()->take(15)->pluck('id')->toArray()
            : [];
    }

    public function updatedSelectedSales(): void
    {
        $visible = $this->buildSalesQuery()->take(15)->pluck('id')->toArray();
        $this->selectAll = ! empty($visible) && count($this->selectedSales) === count($visible);
    }

    public function clearSelection(): void
    {
        $this->selectedSales = [];
        $this->selectAll = false;
    }

    public function confirmBulkDelete(): void
    {
        if (empty($this->selectedSales)) {
            return;
        }

        $this->dispatch('confirm-bulk-delete', ids: $this->selectedSales);
    }

    #[On('bulk-deleted')]
    public function onBulkDeleted(int $count = 0): void
    {
        $this->clearSelection();
        session()->flash('success', __(':count transaksi berhasil dihapus.', ['count' => $count]));
    }

    public function with()
    {
        $user = Auth::user();

        $sales = $this->buildSalesQuery()->take(15)->get();

        $baseQuery = Sale::where('cashier_id', $user->id);

        $summary = [
            'total_transactions' => (clone $baseQuery)->count(),
            'total_revenue' => (clone $baseQuery)->where('status', 'completed')->sum('total'),
            'today_transactions' => (clone $baseQuery)->whereDate('sale_date', today())->count(),
            'today_revenue' => (clone $baseQuery)->whereDate('sale_date', today())->where('status', 'completed')->sum('total'),
        ];

        return [
            'user' => $user,
            'store' => $user->store,
            'sales' => $sales,
            'summary' => $summary,
            'isFiltered' => $this->search || $this->filterStatus || $this->dateFrom || $this->dateTo,
        ];
    }
};
?>

<div>
    <x-page-header title="Riwayat Penjualan" leading="Daftar transaksi yang Anda buat" :time="true" />

    <div class="mx-auto mt-2 max-w-7xl space-y-2">

        {{-- ── FLASH SUCCESS ── --}}
        @if (session('success'))
            <div class="bg-sage-50 dark:bg-sage-950 border-sage-200 dark:border-sage-800 text-sage-700 dark:text-sage-300 flex items-center gap-2.5 rounded-xl border px-3 py-2 text-xs">
                <svg class="h-3.5 w-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                {{ session('success') }}
            </div>
        @endif

        {{-- ⭐ INFO STORE --}}
        @if ($store)
            <div class="rounded-xl border border-stone-200 bg-white px-3 py-3 dark:border-stone-800 dark:bg-stone-900">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-3">
                        <div class="bg-sage-100 dark:bg-sage-900/60 text-sage-700 dark:text-sage-400 flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-xs font-bold uppercase">
                            {{ mb_substr($store->name, 0, 2) }}
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-stone-800 dark:text-stone-100">
                                {{ $store->name }}
                            </p>
                            <p class="text-[10px] text-stone-500 dark:text-stone-400">
                                {{ $store->code }} · {{ $store->location }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <div class="rounded-lg border border-stone-200 bg-stone-50 px-3 py-1.5 dark:border-stone-700 dark:bg-stone-800/50">
                            <p class="text-[9px] uppercase tracking-wider text-stone-500 dark:text-stone-400">
                                {{ __('Kasir') }}
                            </p>
                            <p class="text-[11px] font-semibold text-stone-800 dark:text-stone-100">
                                {{ $user->name }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- SUMMARY CARDS (Personal) --}}
        <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
            <div class="rounded-xl border border-stone-200 bg-white p-3 dark:border-stone-800 dark:bg-stone-900">
                <p class="text-[10px] font-medium uppercase tracking-wider text-stone-500 dark:text-stone-400">
                    {{ __('Transaksi Saya') }}
                </p>
                <p class="mt-0.5 font-mono text-lg font-semibold text-stone-800 dark:text-stone-100">
                    {{ number_format($summary['total_transactions'], 0, ',', '.') }}
                </p>
            </div>
            <div class="rounded-xl border border-stone-200 bg-white p-3 dark:border-stone-800 dark:bg-stone-900">
                <p class="text-[10px] font-medium uppercase tracking-wider text-stone-500 dark:text-stone-400">
                    {{ __('Pendapatan Saya') }}
                </p>
                <p class="mt-0.5 font-mono text-lg font-semibold text-sage-700 dark:text-sage-400">
                    Rp {{ number_format($summary['total_revenue'], 0, ',', '.') }}
                </p>
            </div>
            <div class="rounded-xl border border-stone-200 bg-white p-3 dark:border-stone-800 dark:bg-stone-900">
                <p class="text-[10px] font-medium uppercase tracking-wider text-stone-500 dark:text-stone-400">
                    {{ __('Hari Ini') }}
                </p>
                <p class="mt-0.5 font-mono text-lg font-semibold text-blue-600 dark:text-blue-400">
                    {{ number_format($summary['today_transactions'], 0, ',', '.') }}
                </p>
            </div>
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
                <div class="relative flex-1 min-w-[200px]">
                    <svg class="pointer-events-none absolute left-2.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-stone-400 dark:text-stone-500"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z" />
                    </svg>
                    <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari nomor invoice…"
                        class="focus:ring-sage-500 w-full rounded-lg border border-stone-200 bg-stone-50 py-1.5 pl-8 pr-3 text-xs text-stone-800 transition placeholder:text-stone-400 focus:border-transparent focus:outline-none focus:ring-1 dark:border-stone-700 dark:bg-stone-800 dark:text-stone-100" />
                </div>

                <select wire:model.live="filterStatus"
                    class="focus:ring-sage-500 rounded-lg border border-stone-200 bg-stone-50 px-2.5 py-1.5 text-xs text-stone-700 transition focus:outline-none focus:ring-1 dark:border-stone-700 dark:bg-stone-800 dark:text-stone-300">
                    <option value="">Semua Status</option>
                    <option value="completed">Completed</option>
                    <option value="void">Void</option>
                </select>

                <input wire:model.live="dateFrom" type="date"
                    class="focus:ring-sage-500 rounded-lg border border-stone-200 bg-stone-50 px-2.5 py-1.5 text-xs text-stone-700 transition focus:outline-none focus:ring-1 dark:border-stone-700 dark:bg-stone-800 dark:text-stone-300" />

                <input wire:model.live="dateTo" type="date"
                    class="focus:ring-sage-500 rounded-lg border border-stone-200 bg-stone-50 px-2.5 py-1.5 text-xs text-stone-700 transition focus:outline-none focus:ring-1 dark:border-stone-700 dark:bg-stone-800 dark:text-stone-300" />

                @if ($search || $filterStatus || $dateFrom || $dateTo)
                    <button
                        wire:click="$set('search', ''); $set('filterStatus', ''); $set('dateFrom', ''); $set('dateTo', '')"
                        class="rounded-lg border border-stone-200 px-2.5 py-1.5 text-xs font-medium text-stone-600 transition hover:bg-stone-50 dark:border-stone-700 dark:text-stone-400 dark:hover:bg-stone-800">
                        {{ __('Reset') }}
                    </button>
                @endif
            </div>
        </div>

        {{-- TABLE CARD --}}
        <div class="overflow-hidden rounded-xl border border-stone-200 bg-white px-4 dark:border-stone-800 dark:bg-stone-900">

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
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z" />
                    </svg>
                    {{ __('Memuat…') }}
                </div>
            </div>

            {{-- ═══ BULK ACTION BAR — dipindah ke dalam table card ═══ --}}
            @if (count($selectedSales) > 0)
                <div class="flex items-center justify-between gap-2 border-b border-sage-200 bg-sage-50 px-4 py-2 dark:border-sage-900/50 dark:bg-sage-950/30">
                    <div class="flex items-center gap-2 text-[11px] font-medium text-sage-800 dark:text-sage-300">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        {{ count($selectedSales) }} {{ __('transaksi dipilih') }}
                    </div>

                    <div class="flex items-center gap-1.5">
                        <button wire:click="clearSelection"
                            class="rounded-md border border-stone-200 bg-white px-2.5 py-1 text-[11px] font-medium text-stone-600 transition hover:bg-stone-50 dark:border-stone-700 dark:bg-stone-800 dark:text-stone-400 dark:hover:bg-stone-700">
                            {{ __('Batal Pilih') }}
                        </button>

                        <button wire:click="confirmBulkDelete"
                            class="inline-flex cursor-pointer items-center gap-1.5 rounded-md bg-red-600 px-2.5 py-1 text-[11px] font-medium text-white transition hover:bg-red-700 dark:bg-red-500 dark:hover:bg-red-600">
                            <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                            </svg>
                            {{ __('Hapus Terpilih') }}
                        </button>
                    </div>
                </div>
            @endif

            {{-- Table --}}
            <div class="overflow-x-auto rounded-xl border bg-white shadow-sm dark:border-stone-800 dark:bg-stone-900">
                <table class="w-full border-collapse text-left text-[11px]">
                    <thead>
                        <tr
                            class="border-b border-stone-200 bg-stone-50 font-semibold uppercase tracking-wider text-stone-500 dark:border-stone-800 dark:bg-stone-800/50 dark:text-stone-400">
                            <th class="w-8 px-2.5 py-1.5 text-center">
                                <input type="checkbox" wire:model.live="selectAll"
                                    class="focus:ring-sage-500 h-3.5 w-3.5 cursor-pointer rounded border-stone-300 text-sage-600 focus:ring-1 dark:border-stone-600 dark:bg-stone-700" />
                            </th>
                            <th class="w-6 px-2.5 py-1.5 text-center">#</th>
                            <th class="px-2.5 py-1.5">{{ __('Invoice') }}</th>
                            <th class="hidden px-2.5 py-1.5 sm:table-cell">{{ __('Tanggal') }}</th>
                            <th class="hidden w-16 px-2.5 py-1.5 text-center md:table-cell">{{ __('Item') }}</th>
                            <th class="px-2.5 py-1.5 text-right">{{ __('Total') }}</th>
                            <th class="hidden w-20 px-2.5 py-1.5 text-center sm:table-cell">{{ __('Status') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100 dark:divide-stone-800/60">
                        @forelse ($sales as $sale)
                            <tr x-data
                                @click="$dispatch('open-detail-sale-modal', { saleId: {{ $sale->id }} })"
                                class="group cursor-pointer transition-colors hover:bg-stone-50 dark:hover:bg-stone-800/30">
                                <td class="px-2.5 py-1.5 text-center">
                                    <input type="checkbox" wire:model.live="selectedSales" value="{{ $sale->id }}"
                                        class="focus:ring-sage-500 h-3.5 w-3.5 cursor-pointer rounded border-stone-300 text-sage-600 focus:ring-1 dark:border-stone-600 dark:bg-stone-700" />
                                </td>
                                <td class="px-2.5 py-1.5 text-center font-mono text-stone-400 dark:text-stone-600">
                                    {{ $loop->iteration }}
                                </td>
                                <td class="px-2.5 py-1.5 font-mono font-medium text-stone-800 dark:text-stone-200">
                                    {{ $sale->invoice_number }}
                                </td>
                                <td class="hidden px-2.5 py-1.5 text-stone-500 sm:table-cell dark:text-stone-400">
                                    {{ $sale->sale_date->format('d M Y, H:i') }}
                                </td>
                                <td class="hidden px-2.5 py-1.5 text-center md:table-cell">
                                    <span class="inline-flex items-center rounded-full bg-stone-100 px-2 py-0.5 text-[10px] font-semibold text-stone-600 dark:bg-stone-800 dark:text-stone-400">
                                        {{ $sale->items->count() }}
                                    </span>
                                </td>
                                <td class="px-2.5 py-1.5 text-right font-mono font-medium text-sage-700 dark:text-sage-400">
                                    Rp {{ number_format($sale->total, 0, ',', '.') }}
                                </td>
                                <td class="hidden px-2.5 py-1.5 text-center sm:table-cell">
                                    @php
                                        $statusClasses =
                                            [
                                                'completed' => 'bg-green-50 text-green-700 dark:bg-green-950/40 dark:text-green-400',
                                                'void' => 'bg-red-50 text-red-700 dark:bg-red-950/40 dark:text-red-400',
                                            ][$sale->status] ??
                                            'bg-stone-100 text-stone-600 dark:bg-stone-800 dark:text-stone-400';
                                    @endphp
                                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $statusClasses }}">
                                        {{ ucfirst($sale->status) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-2.5 py-10 text-center">
                                    <div class="flex flex-col items-center gap-1.5 text-stone-400">
                                        <svg class="h-8 w-8 text-stone-300 dark:text-stone-600" fill="none"
                                            stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        <p class="text-xs font-medium">{{ __('Belum ada transaksi') }}</p>
                                        <p class="text-[10px] text-stone-400 dark:text-stone-500">
                                            {{ __('Transaksi yang Anda buat akan muncul di sini') }}
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Modal detail sale --}}
    <livewire:user.sales.detail-sale />

    {{-- Modal konfirmasi bulk delete (child) --}}
    <livewire:admin.sales.bulk-delete-sales-modal />
</div>
