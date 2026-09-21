<?php

use Livewire\Component;
use App\Models\Sale;

new class extends Component {
    public ?int $saleId = null;
    public ?Sale $sale = null;
    public bool $isLoading = false;

    public function openDetail(int $saleId): void
    {
        $this->isLoading = true;
        $this->saleId = $saleId;
        $this->sale = Sale::with(['store', 'items.product'])->findOrFail($saleId);
        $this->isLoading = false;
    }

    public function clearDetail(): void
    {
        $this->saleId = null;
        $this->sale = null;
    }
};
?>

<div>
    <div x-data="{
        show: false,
        init() {
            window.addEventListener('open-detail-sale-modal', (e) => {
                $wire.openDetail(e.detail.saleId);
                this.show = true;
            });
        }
    }" x-show="show" x-transition.opacity
        @keydown.escape.window="show = false; $wire.clearDetail()"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-3 text-xs sm:p-4" x-cloak
        @click.self="show = false; $wire.clearDetail()">

        <div class="flex max-h-[90vh] w-full max-w-2xl flex-col overflow-hidden rounded-lg bg-white shadow-xl dark:bg-stone-900"
            @click.stop>

            {{-- Loading --}}
            <div wire:loading wire:target="openDetail"
                class="absolute inset-0 z-50 flex items-center justify-center bg-white/60 backdrop-blur-[0.5px] dark:bg-stone-900/60">
                <div class="flex items-center gap-1.5 rounded-md border border-stone-100 bg-white px-2.5 py-1.5 shadow-sm dark:border-stone-700 dark:bg-stone-800">
                    <svg class="h-3.5 w-3.5 animate-spin text-sage-600 dark:text-sage-400" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V12H4z"></path>
                    </svg>
                    <span class="text-[10px] font-medium text-stone-600 dark:text-stone-300">
                        {{ __('Memuat detail...') }}
                    </span>
                </div>
            </div>

            @if ($sale)
                <div class="flex min-h-0 flex-1 flex-col">

                    {{-- HEADER --}}
                    <div class="flex-shrink-0 space-y-3 border-b border-stone-100 p-4 pb-3 dark:border-stone-800">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2">
                                    <h3 class="font-mono text-sm font-semibold text-stone-800 dark:text-stone-100">
                                        {{ $sale->invoice_number }}
                                    </h3>
                                    @php
                                        $statusClasses = [
                                            'completed' => 'bg-green-50 text-green-700 dark:bg-green-950/40 dark:text-green-400',
                                            'void' => 'bg-red-50 text-red-700 dark:bg-red-950/40 dark:text-red-400',
                                            'refunded' => 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400',
                                        ][$sale->status] ?? 'bg-stone-100 text-stone-600 dark:bg-stone-800 dark:text-stone-400';
                                    @endphp
                                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $statusClasses }}">
                                        {{ ucfirst($sale->status) }}
                                    </span>
                                </div>
                                <p class="mt-0.5 text-[11px] text-stone-500 dark:text-stone-400">
                                    {{ $sale->sale_date->format('d M Y, H:i') }}
                                </p>
                            </div>

                            {{-- Close --}}
                            <button type="button" @click="show = false; $wire.clearDetail()"
                                class="cursor-pointer rounded-md p-1 text-stone-400 transition hover:bg-stone-100 hover:text-stone-700 dark:hover:bg-stone-800 dark:hover:text-stone-200">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        {{-- Meta --}}
                        <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                            <div class="rounded-md border border-stone-200 bg-stone-50 px-2.5 py-1.5 dark:border-stone-700 dark:bg-stone-800/50">
                                <p class="text-[10px] uppercase tracking-wider text-stone-500 dark:text-stone-400">{{ __('Kasir') }}</p>
                                <p class="mt-0.5 truncate text-[11px] font-medium text-stone-800 dark:text-stone-200">
                                    {{ $sale->cashier_name }}
                                </p>
                            </div>
                            <div class="rounded-md border border-stone-200 bg-stone-50 px-2.5 py-1.5 dark:border-stone-700 dark:bg-stone-800/50">
                                <p class="text-[10px] uppercase tracking-wider text-stone-500 dark:text-stone-400">{{ __('Store') }}</p>
                                <p class="mt-0.5 truncate text-[11px] font-medium text-stone-800 dark:text-stone-200">
                                    {{ $sale->store?->name ?? '—' }}
                                </p>
                            </div>
                            <div class="col-span-2 rounded-md border border-stone-200 bg-stone-50 px-2.5 py-1.5 sm:col-span-1 dark:border-stone-700 dark:bg-stone-800/50">
                                <p class="text-[10px] uppercase tracking-wider text-stone-500 dark:text-stone-400">{{ __('Total Item') }}</p>
                                <p class="mt-0.5 text-[11px] font-medium text-stone-800 dark:text-stone-200">
                                    {{ $sale->items->count() }} {{ __('item') }}
                                    ·
                                    {{ $sale->items->sum('quantity') }} {{ __('qty') }}
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- BODY (scrollable) --}}
                    <div class="min-h-0 flex-1 overflow-y-auto">
                        <table class="w-full text-left text-[11px]">
                            <thead class="sticky top-0 z-10">
                                <tr class="border-b border-stone-200 bg-stone-50 font-semibold uppercase tracking-wider text-stone-500 dark:border-stone-800 dark:bg-stone-800 dark:text-stone-400">
                                    <th class="px-4 py-1.5">{{ __('Produk') }}</th>
                                    <th class="px-3 py-1.5 text-right">{{ __('Harga') }}</th>
                                    <th class="px-3 py-1.5 text-center">{{ __('Qty') }}</th>
                                    <th class="px-4 py-1.5 text-right">{{ __('Subtotal') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-stone-100 dark:divide-stone-800/60">
                                @foreach ($sale->items as $item)
                                    @php
                                        $isDiscountUsed = $item->discount_price
                                            && $item->discount_price < $item->price;
                                    @endphp
                                    <tr class="transition-colors hover:bg-stone-50 dark:hover:bg-stone-800/30">
                                        <td class="px-4 py-2">
                                            <div class="flex items-center gap-2">
                                                @if ($item->product?->image)
                                                    <img src="{{ \Illuminate\Support\Facades\Storage::url($item->product->image) }}"
                                                        alt="{{ $item->product_name }}"
                                                        class="h-7 w-7 shrink-0 rounded-md border border-stone-200 object-cover dark:border-stone-700" />
                                                @else
                                                    <div class="bg-sage-100 dark:bg-sage-900/60 text-sage-700 dark:text-sage-400 flex h-7 w-7 shrink-0 items-center justify-center rounded-md text-[9px] font-bold uppercase">
                                                        {{ mb_substr($item->product_name, 0, 2) }}
                                                    </div>
                                                @endif
                                                <div class="min-w-0">
                                                    <div class="truncate font-medium text-stone-800 dark:text-stone-200">
                                                        {{ $item->product_name }}
                                                    </div>
                                                    <div class="truncate font-mono text-[10px] text-stone-500 dark:text-stone-400">
                                                        {{ $item->product_code ?? '—' }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>

                                        <td class="px-3 py-2 text-right">
                                            @php
                                                $isDiscountUsed = $item->discount_price
                                                    && $item->effective_price !== null
                                                    && (float) $item->effective_price === (float) $item->discount_price;
                                            @endphp

                                            @if ($isDiscountUsed)
                                                {{-- Diskon dipakai: coret harga asli, tampilkan harga diskon --}}
                                                <div class="font-mono text-[10px] text-stone-400 line-through dark:text-stone-500">
                                                    Rp {{ number_format($item->price, 0, ',', '.') }}
                                                </div>
                                                <div class="font-mono font-medium text-green-600 dark:text-green-400">
                                                    Rp {{ number_format($item->effective_price, 0, ',', '.') }}
                                                </div>
                                            @else
                                                {{-- Tidak pakai diskon: tampilkan harga normal --}}
                                                <div class="font-mono text-stone-700 dark:text-stone-300">
                                                    Rp {{ number_format($item->price, 0, ',', '.') }}
                                                </div>
                                            @endif
                                        </td>

                                        <td class="px-3 py-2 text-center">
                                            <span class="inline-flex items-center rounded-md bg-stone-100 px-2 py-0.5 font-mono text-[10px] font-semibold text-stone-700 dark:bg-stone-800 dark:text-stone-300">
                                                {{ $item->quantity }}×
                                            </span>
                                        </td>

                                        <td class="px-4 py-2 text-right font-mono font-medium text-stone-800 dark:text-stone-200">
                                            Rp {{ number_format($item->line_total, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- FOOTER — Summary --}}
                    <div class="flex-shrink-0 border-t border-stone-100 bg-stone-50 p-4 dark:border-stone-800 dark:bg-stone-800/30">
                        <div class="space-y-1 text-[11px]">
                            <div class="flex justify-between">
                                <span class="text-stone-500 dark:text-stone-400">{{ __('Subtotal') }}</span>
                                <span class="font-mono text-stone-700 dark:text-stone-300">
                                    Rp {{ number_format($sale->subtotal, 0, ',', '.') }}
                                </span>
                            </div>

                            @if ($sale->discount_amount > 0)
                                <div class="flex justify-between">
                                    <span class="text-stone-500 dark:text-stone-400">{{ __('Diskon') }}</span>
                                    <span class="font-mono text-red-600 dark:text-red-400">
                                        - Rp {{ number_format($sale->discount_amount, 0, ',', '.') }}
                                    </span>
                                </div>
                            @endif

                            @if ($sale->tax_amount > 0)
                                <div class="flex justify-between">
                                    <span class="text-stone-500 dark:text-stone-400">{{ __('Pajak') }}</span>
                                    <span class="font-mono text-stone-700 dark:text-stone-300">
                                        Rp {{ number_format($sale->tax_amount, 0, ',', '.') }}
                                    </span>
                                </div>
                            @endif

                            <div class="flex justify-between border-t border-stone-200 pt-1.5 dark:border-stone-700">
                                <span class="text-xs font-semibold text-stone-800 dark:text-stone-100">{{ __('Total') }}</span>
                                <span class="font-mono text-base font-bold text-sage-700 dark:text-sage-400">
                                    Rp {{ number_format($sale->total, 0, ',', '.') }}
                                </span>
                            </div>

                            <div class="flex justify-between">
                                <span class="text-stone-500 dark:text-stone-400">{{ __('Bayar') }}</span>
                                <span class="font-mono text-stone-700 dark:text-stone-300">
                                    Rp {{ number_format($sale->payment_amount, 0, ',', '.') }}
                                </span>
                            </div>

                            <div class="flex justify-between">
                                <span class="text-stone-500 dark:text-stone-400">{{ __('Kembalian') }}</span>
                                <span class="font-mono font-medium text-blue-600 dark:text-blue-400">
                                    Rp {{ number_format($sale->change_amount, 0, ',', '.') }}
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="flex flex-shrink-0 justify-end gap-1.5 border-t border-stone-100 bg-white p-4 pt-3 dark:border-stone-800 dark:bg-stone-900">
                        <button type="button" @click="show = false; $wire.clearDetail()"
                            class="cursor-pointer rounded-md border border-stone-200 px-3 py-1.5 text-xs font-medium text-stone-600 transition hover:bg-stone-50 dark:border-stone-700 dark:text-stone-400 dark:hover:bg-stone-800">
                            {{ __('Tutup') }}
                        </button>

                        <button type="button" onclick="window.print()"
                            class="bg-sage-600 hover:bg-sage-700 focus:ring-sage-500 dark:bg-sage-500 dark:hover:bg-sage-600 inline-flex cursor-pointer items-center gap-1.5 rounded-md px-3 py-1.5 text-xs font-medium text-white transition focus:outline-none focus:ring-2 focus:ring-offset-1">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z" />
                            </svg>
                            {{ __('Cetak') }}
                        </button>
                    </div>
                </div>
            @endif

        </div>
    </div>
</div>
