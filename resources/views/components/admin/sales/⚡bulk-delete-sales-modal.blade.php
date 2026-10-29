<?php

use Livewire\Component;
use App\Models\Sale;

new class extends Component {
    public array $ids = [];

    public function open(array $ids): void
    {
        $this->ids = $ids;
        $this->dispatch('bulk-delete-opened');
    }

    public function delete(): void
    {
        if (empty($this->ids)) return;

        // Hapus transaksi beserta items (pastikan FK onDelete cascade di migration sale_items)
        $count = Sale::whereIn('id', $this->ids)->delete();

        // Log untuk audit
        \Log::warning('Bulk delete sales', [
            'admin_id' => auth()->id(),
            'count' => $count,
            'sale_ids' => $this->ids,
        ]);

        $this->dispatch('bulk-deleted', count: $count);
        $this->dispatch('bulk-delete-closed');

        $this->ids = [];
    }

    public function close(): void
    {
        $this->ids = [];
        $this->dispatch('bulk-delete-closed');
    }
};
?>

<div>
    <div x-data="{
        show: false,
        init() {
            window.addEventListener('confirm-bulk-delete', (e) => {
                $wire.open(e.detail.ids);
            });
            window.addEventListener('bulk-delete-opened', () => {
                this.show = true;
            });
            window.addEventListener('bulk-delete-closed', () => {
                this.show = false;
            });
        }
    }" x-show="show" x-transition.opacity
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-3 text-xs sm:p-4" x-cloak
        @click.self="$wire.close()">

        <div class="flex max-h-[90vh] w-full max-w-md flex-col overflow-hidden rounded-lg bg-white shadow-xl dark:bg-stone-900"
            @click.stop>

            {{-- HEADER --}}
            <div class="flex-shrink-0 space-y-3 p-4 pb-3">
                <div class="flex items-start gap-3">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-red-100 dark:bg-red-950/50">
                        <svg class="h-4.5 w-4.5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor"
                            stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-sm font-semibold text-stone-800 dark:text-stone-100">
                            {{ __('Hapus Transaksi?') }}
                        </h3>
                        <p class="mt-0.5 text-[11px] leading-normal text-stone-500 dark:text-stone-400">
                            {{ __('Anda akan menghapus :count transaksi secara permanen.', ['count' => count($ids)]) }}
                        </p>
                    </div>
                </div>
                <div class="border-t border-stone-100 dark:border-stone-800"></div>
            </div>

            {{-- BODY --}}
            <div class="min-h-0 flex-1 overflow-y-auto px-4">
                <div class="relative">
                    {{-- Loading overlay --}}
                    <div wire:loading wire:target="delete"
                        class="absolute inset-0 z-50 flex items-center justify-center rounded-md bg-white/60 backdrop-blur-[0.5px] dark:bg-stone-900/60">
                        <div class="flex items-center gap-1.5 rounded-md border border-stone-100 bg-white px-2.5 py-1.5 shadow-sm dark:border-stone-700 dark:bg-stone-800">
                            <svg class="h-3.5 w-3.5 animate-spin text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V12H4z"></path>
                            </svg>
                            <span class="text-[10px] font-medium text-stone-600 dark:text-stone-300">
                                {{ __('Menghapus...') }}
                            </span>
                        </div>
                    </div>

                    <div class="pb-4">
                        <div class="rounded-md border border-red-200 bg-red-50 px-3 py-2 text-[11px] text-red-800 dark:border-red-900/50 dark:bg-red-950/30 dark:text-red-300">
                            <p class="font-medium">{{ __('Perhatian!') }}</p>
                            <p class="mt-0.5">
                                {{ __('Data transaksi yang dihapus tidak dapat dikembalikan. Termasuk semua item dan detail transaksi di dalamnya.') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- FOOTER --}}
            <div class="flex flex-shrink-0 justify-end gap-1.5 border-t border-stone-100 bg-white p-4 pt-3 dark:border-stone-800 dark:bg-stone-900">
                <button type="button" wire:click="close" wire:loading.attr="disabled" wire:target="delete"
                    class="rounded-md border border-stone-200 px-3 py-1.5 text-xs font-medium text-stone-600 transition hover:bg-stone-50 disabled:cursor-not-allowed disabled:opacity-60 dark:border-stone-700 dark:text-stone-400 dark:hover:bg-stone-800">
                    {{ __('Batal') }}
                </button>

                <button type="button" wire:click="delete" wire:loading.attr="disabled" wire:target="delete"
                    class="cursor-pointer rounded-md bg-red-600 px-3 py-1.5 text-xs font-medium text-white transition hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-1 disabled:cursor-not-allowed disabled:opacity-60 dark:bg-red-500 dark:hover:bg-red-600">
                    {{ __('Ya, Hapus Semua') }}
                </button>
            </div>

        </div>
    </div>
</div>
