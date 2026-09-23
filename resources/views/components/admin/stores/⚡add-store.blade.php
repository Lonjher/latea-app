<?php

use Livewire\Component;
use Livewire\Attributes\On;
use App\Livewire\Forms\StoreForm;
use Illuminate\Validation\ValidationException;

new class extends Component {
    public StoreForm $form;

    public function create()
    {
        try {
            $this->form->create();
            session()->flash('success', 'Store berhasil ditambahkan.');
            $this->dispatch('store-added', message: 'Store berhasil ditambahkan.');
        } catch (ValidationException $e) {
            session()->flash('error', 'Terjadi kesalahan saat membuat store: ' . $e->getMessage());
            $this->dispatch('store-error', message: 'Terjadi kesalahan saat membuat store: ' . $e->getMessage());
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
            window.addEventListener('add-store-modal', () => {
                this.errorMessage = '';
                this.show = true;
            });
            window.addEventListener('store-error', (e) => {
                this.errorMessage = e.detail.message;
            });
            window.addEventListener('store-added', (e) => {
                this.show = false;
                this.successMessage = e.detail.message;
            });
        }
    }" x-show="show" x-transition.opacity
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-3 text-xs sm:p-4" x-cloak
        @click.self="show = false">

        {{-- Container modal: flex-col supaya header/body/footer terpisah --}}
        <div class="flex max-h-[90vh] w-full max-w-md flex-col overflow-hidden rounded-lg bg-white shadow-xl dark:bg-stone-900"
            @click.stop>

            <form wire:submit.prevent="create" class="flex min-h-0 flex-1 flex-col">

                {{-- HEADER (fixed) --}}
                <div class="flex-shrink-0 space-y-3 p-4 pb-3">
                    <div>
                        <h3 class="text-sm font-semibold text-stone-800 dark:text-stone-100">
                            {{ __('Add Store Baru') }}
                        </h3>
                        <p class="mt-0.5 text-[11px] leading-normal text-stone-500 dark:text-stone-400">
                            {{ __('Isi data detail store untuk store baru.') }}
                        </p>
                    </div>
                    <div class="border-t border-stone-100 dark:border-stone-800"></div>
                </div>

                {{-- BODY (scrollable) --}}
                <div class="min-h-0 flex-1 overflow-y-auto px-4">
                    {{-- Pesan Error Global --}}
                    <div x-show="errorMessage" x-cloak
                        class="mb-2 rounded-md border border-red-200 bg-red-50 px-2.5 py-1.5 text-[11px] text-red-700 dark:border-red-900/50 dark:bg-red-950/30 dark:text-red-300"
                        x-text="errorMessage">
                    </div>
                    <div x-show="successMessage" x-cloak
                        class="mb-2 rounded-md border border-green-200 bg-green-50 px-2.5 py-1.5 text-[11px] text-green-700 dark:border-green-900/50 dark:bg-green-950/30 dark:text-green-300"
                        x-text="successMessage">
                    </div>

                    <div class="relative">
                        {{-- Indikator Loading --}}
                        <div wire:loading wire:target="create"
                            class="absolute inset-0 z-50 flex items-center justify-center rounded-md bg-white/60 backdrop-blur-[0.5px] dark:bg-stone-900/60">
                            <div
                                class="flex items-center gap-1.5 rounded-md border border-stone-100 bg-white px-2.5 py-1.5 shadow-sm dark:border-stone-700 dark:bg-stone-800">
                                <svg class="h-3.5 w-3.5 animate-spin text-sage-600 dark:text-sage-400" fill="none"
                                    viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                        stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V12H4z"></path>
                                </svg>
                                <span class="text-[10px] font-medium text-stone-600 dark:text-stone-300">
                                    {{ __('Menyimpan data...') }}
                                </span>
                            </div>
                        </div>

                        {{-- Grid Form Input --}}
                        <div class="space-y-2.5 pb-4">
                            {{-- Baris 1: Name & Code --}}
                            <div class="grid grid-cols-1 gap-2.5 sm:grid-cols-2">
                                <div>
                                    <label class="mb-0.5 block text-[11px] font-medium text-stone-600 dark:text-stone-400">
                                        {{ __('Store Name') }}
                                    </label>
                                    <input wire:model="form.name" type="text" required
                                        class="w-full rounded-md border border-stone-200 bg-stone-50 px-2.5 py-1.5 text-xs text-stone-800 placeholder:text-stone-400 focus:border-sage-500 focus:outline-none focus:ring-1 focus:ring-sage-500 dark:border-stone-700 dark:bg-stone-800 dark:text-stone-100" />
                                    @error('form.name')
                                        <p class="mt-0.5 text-[10px] text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="mb-0.5 block text-[11px] font-medium text-stone-600 dark:text-stone-400">
                                        {{ __('Code') }}
                                    </label>
                                    <input wire:model="form.code" type="text" maxlength="8" placeholder="STORE....."
                                        required
                                        class="w-full rounded-md border border-stone-200 bg-stone-50 px-2.5 py-1.5 text-xs text-stone-800 placeholder:text-stone-400 focus:border-sage-500 focus:outline-none focus:ring-1 focus:ring-sage-500 dark:border-stone-700 dark:bg-stone-800 dark:text-stone-100" />
                                    @error('form.code')
                                        <p class="mt-0.5 text-[10px] text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            {{-- Baris 2: Location --}}
                            <div>
                                <label class="mb-0.5 block text-[11px] font-medium text-stone-600 dark:text-stone-400">
                                    {{ __('Location') }}
                                </label>
                                <input wire:model="form.location" type="text" placeholder="Location of the store"
                                    required
                                    class="w-full rounded-md border border-stone-200 bg-stone-50 px-2.5 py-1.5 text-xs text-stone-800 placeholder:text-stone-400 focus:border-sage-500 focus:outline-none focus:ring-1 focus:ring-sage-500 dark:border-stone-700 dark:bg-stone-800 dark:text-stone-100" />
                                @error('form.location')
                                    <p class="mt-0.5 text-[10px] text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Baris 3: Active --}}
                            <div>
                                <label class="mb-0.5 block text-[11px] font-medium text-stone-600 dark:text-stone-400">
                                    {{ __('Active') }}
                                </label>
                                <select wire:model="form.is_active" required
                                    class="w-full rounded-md border border-stone-200 bg-stone-50 px-2.5 py-1.5 text-xs text-stone-800 focus:border-sage-500 focus:outline-none focus:ring-1 focus:ring-sage-500 dark:border-stone-700 dark:bg-stone-800 dark:text-stone-100">
                                    <option value="">-- Select Active Status --</option>
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                                @error('form.is_active')
                                    <p class="mt-0.5 text-[10px] text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- FOOTER (fixed di bawah) --}}
                <div
                    class="flex flex-shrink-0 justify-end gap-1.5 border-t border-stone-100 bg-white p-4 pt-3 dark:border-stone-800 dark:bg-stone-900">
                    <button type="button" @click="show = false"
                        class="rounded-md border border-stone-200 px-3 py-1.5 text-xs font-medium text-stone-600 transition hover:bg-stone-50 dark:border-stone-700 dark:text-stone-400 dark:hover:bg-stone-800">
                        {{ __('Batal') }}
                    </button>

                    <button type="submit" wire:loading.attr="disabled" wire:target="create"
                        class="rounded-md bg-sage-600 px-3 py-1.5 text-xs font-medium text-white transition hover:bg-sage-700 focus:outline-none focus:ring-2 focus:ring-sage-500 focus:ring-offset-1 disabled:cursor-not-allowed disabled:opacity-60 dark:bg-sage-500 dark:hover:bg-sage-600">
                        {{ __('Simpan') }}
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>
