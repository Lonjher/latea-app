<?php

use Livewire\Component;
use App\Livewire\Forms\OperationalForm;
use Illuminate\Validation\ValidationException;

new class extends Component {
    public OperationalForm $form;

    public function create()
    {
        try {
            $this->form->create();
            session()->flash('success', 'Operational berhasil ditambahkan.');
            $this->dispatch('operational-added', message: 'Operational berhasil ditambahkan.');
        } catch (ValidationException $e) {
            $this->dispatch('operational-error', message: 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
};
?>

<div>
    <div x-data="{
        show: false,
        errorMessage: '',
        successMessage: '',
        init() {
            window.addEventListener('add-operational-modal', () => {
                this.errorMessage = '';
                this.show = true;
            });
            window.addEventListener('operational-error', (e) => {
                this.errorMessage = e.detail.message;
            });
            window.addEventListener('operational-added', (e) => {
                this.show = false;
                this.successMessage = e.detail.message;
            });
        }
    }" x-show="show" x-transition.opacity
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-3 text-xs sm:p-4" x-cloak
        @click.self="show = false">

        <div class="flex max-h-[90vh] w-full max-w-md flex-col overflow-hidden rounded-lg bg-white shadow-xl dark:bg-stone-900"
            @click.stop>

            <form wire:submit.prevent="create" class="flex min-h-0 flex-1 flex-col">

                {{-- HEADER --}}
                <div class="flex-shrink-0 space-y-3 p-4 pb-3">
                    <div>
                        <h3 class="text-sm font-semibold text-stone-800 dark:text-stone-100">
                            {{ __('Add Operational') }}
                        </h3>
                        <p class="mt-0.5 text-[11px] leading-normal text-stone-500 dark:text-stone-400">
                            {{ __('Tambahkan biaya operasional untuk toko Anda.') }}
                        </p>
                    </div>
                    <div class="border-t border-stone-100 dark:border-stone-800"></div>
                </div>

                {{-- BODY --}}
                <div class="min-h-0 flex-1 overflow-y-auto px-4">
                    <div x-show="errorMessage" x-cloak
                        class="mb-2 rounded-md border border-red-200 bg-red-50 px-2.5 py-1.5 text-[11px] text-red-700 dark:border-red-900/50 dark:bg-red-950/30 dark:text-red-300"
                        x-text="errorMessage"></div>

                    <div class="relative">
                        {{-- Loading --}}
                        <div wire:loading wire:target="create"
                            class="absolute inset-0 z-50 flex items-center justify-center rounded-md bg-white/60 backdrop-blur-[0.5px] dark:bg-stone-900/60">
                            <div class="flex items-center gap-1.5 rounded-md border border-stone-100 bg-white px-2.5 py-1.5 shadow-sm dark:border-stone-700 dark:bg-stone-800">
                                <svg class="text-sage-600 dark:text-sage-400 h-3.5 w-3.5 animate-spin" fill="none"
                                    viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V12H4z"></path>
                                </svg>
                                <span class="text-[10px] font-medium text-stone-600 dark:text-stone-300">
                                    {{ __('Menyimpan data...') }}
                                </span>
                            </div>
                        </div>

                        {{-- Fields --}}
                        <div class="space-y-2.5 pb-4">

                            {{-- Nama Operational --}}
                            <div>
                                <label class="mb-0.5 block text-[11px] font-medium text-stone-600 dark:text-stone-400">
                                    {{ __('Nama Operational') }}
                                </label>
                                <input wire:model="form.operational_name" type="text" required
                                    placeholder="cth: Listrik, Sewa, Gaji"
                                    class="w-full rounded-md border border-stone-200 bg-stone-50 px-2.5 py-1.5 text-xs text-stone-800 placeholder:text-stone-400 focus:border-sage-500 focus:outline-none focus:ring-1 focus:ring-sage-500 dark:border-stone-700 dark:bg-stone-800 dark:text-stone-100" />
                                @error('form.operational_name')
                                    <p class="mt-0.5 text-[10px] text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Cost --}}
                            <div>
                                <label class="mb-0.5 block text-[11px] font-medium text-stone-600 dark:text-stone-400">
                                    {{ __('Biaya') }}
                                </label>
                                <input wire:model="form.cost" type="number" step="0.01" min="0" required
                                    placeholder="0"
                                    class="w-full rounded-md border border-stone-200 bg-stone-50 px-2.5 py-1.5 text-xs text-stone-800 placeholder:text-stone-400 focus:border-sage-500 focus:outline-none focus:ring-1 focus:ring-sage-500 dark:border-stone-700 dark:bg-stone-800 dark:text-stone-100" />
                                <p class="mt-0.5 text-[10px] text-stone-400 dark:text-stone-500">
                                    {{ __('Nominal biaya dalam Rupiah.') }}
                                </p>
                                @error('form.cost')
                                    <p class="mt-0.5 text-[10px] text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- FOOTER --}}
                <div class="flex flex-shrink-0 justify-end gap-1.5 border-t border-stone-100 bg-white p-4 pt-3 dark:border-stone-800 dark:bg-stone-900">
                    <button type="button" @click="show = false"
                        class="cursor-pointer rounded-md border border-stone-200 px-3 py-1.5 text-xs font-medium text-stone-600 transition hover:bg-stone-50 dark:border-stone-700 dark:text-stone-400 dark:hover:bg-stone-800">
                        {{ __('Batal') }}
                    </button>

                    <button type="submit" wire:loading.attr="disabled" wire:target="create"
                        class="bg-sage-600 hover:bg-sage-700 focus:ring-sage-500 dark:bg-sage-500 dark:hover:bg-sage-600 cursor-pointer rounded-md px-3 py-1.5 text-xs font-medium text-white transition focus:outline-none focus:ring-2 focus:ring-offset-1 disabled:cursor-not-allowed disabled:opacity-60">
                        {{ __('Simpan') }}
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>
