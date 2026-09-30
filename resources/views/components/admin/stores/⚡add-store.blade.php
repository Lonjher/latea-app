<?php

use Livewire\Component;
use Livewire\Attributes\On;
use App\Livewire\Forms\StoreForm;
use Illuminate\Validation\ValidationException;
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;
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
                            {{ __('Tambah Toko Baru') }}
                        </h3>
                        <p class="mt-0.5 text-[11px] leading-normal text-stone-500 dark:text-stone-400">
                            {{ __('Isi data detail toko untuk toko baru.') }}
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
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V12H4z"></path>
                                </svg>
                                <span class="text-[10px] font-medium text-stone-600 dark:text-stone-300">
                                    {{ __('Menyimpan data...') }}
                                </span>
                            </div>
                        </div>

                        {{-- Grid Form Input --}}
                        <div class="space-y-2.5 pb-4">
                            {{-- Baris 0: Image Upload dengan Preview --}}
                            <div>
                                <label class="mb-0.5 block text-[11px] font-medium text-stone-600 dark:text-stone-400">
                                    {{ __('Avatar Toko') }}
                                </label>

                                <div x-data="{
                                    previewUrl: null,
                                    fileName: '',
                                    handleFile(e) {
                                        const file = e.target.files[0];
                                        if (!file) {
                                            this.previewUrl = null;
                                            this.fileName = '';
                                            return;
                                        }
                                        this.fileName = file.name;
                                        const reader = new FileReader();
                                        reader.onload = (ev) => { this.previewUrl = ev.target.result; };
                                        reader.readAsDataURL(file);
                                    }
                                }">

                                    {{-- Area Upload — ⭐ seluruh area bisa diklik --}}
                                    <label x-show="!previewUrl"
                                        class="flex cursor-pointer flex-col items-center justify-center rounded-md border-2 border-dashed border-stone-200 bg-stone-50 px-3 py-4 text-center transition hover:border-sage-400 hover:bg-stone-100 dark:border-stone-700 dark:bg-stone-800/50 dark:hover:border-sage-600 dark:hover:bg-stone-800">
                                        <svg class="mb-1.5 h-6 w-6 text-stone-400 dark:text-stone-500" fill="none"
                                            stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                        </svg>

                                        <span class="text-sage-600 dark:text-sage-400 text-[11px] font-medium">
                                            {{ __('Klik untuk upload') }}
                                        </span>
                                        <p class="mt-1 text-[10px] text-stone-400 dark:text-stone-500">
                                            {{ __('PNG, JPG, WEBP maks 2MB') }}
                                        </p>

                                        {{-- ⭐ Input hidden di dalam label — klik di mana saja pada label akan trigger --}}
                                        <input wire:model="form.image" type="file" accept="image/*"
                                            class="hidden" x-on:change="handleFile($event)" />
                                    </label>

                                    {{-- Preview --}}
                                    <div x-show="previewUrl" x-cloak
                                        class="relative overflow-hidden rounded-md border border-stone-200 bg-stone-50 p-2 dark:border-stone-700 dark:bg-stone-800">
                                        <div class="flex items-center gap-2.5">
                                            <img :src="previewUrl" alt="Preview"
                                                class="h-16 w-16 rounded-md border border-stone-200 object-cover dark:border-stone-700" />
                                            <div class="min-w-0 flex-1">
                                                <p class="truncate text-[11px] font-medium text-stone-700 dark:text-stone-200"
                                                    x-text="fileName"></p>
                                                <p class="text-[10px] text-stone-400 dark:text-stone-500">
                                                    {{ __('Siap diupload') }}
                                                </p>
                                            </div>
                                            <label
                                                class="cursor-pointer rounded-md p-1.5 text-stone-400 transition hover:bg-stone-100 hover:text-red-600 dark:hover:bg-stone-700 dark:hover:text-red-400"
                                                title="Ganti gambar">
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor"
                                                    stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" />
                                                </svg>
                                                <input wire:model="form.image" type="file" accept="image/*"
                                                    class="hidden" x-on:change="handleFile($event)" />
                                            </label>
                                        </div>
                                    </div>

                                    {{-- Loading upload --}}
                                    <div wire:loading wire:target="form.image"
                                        class="text-sage-600 dark:text-sage-400 mt-1 flex items-center gap-1.5 text-[10px]">
                                        <svg class="h-3 w-3 animate-spin" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10"
                                                stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z">
                                            </path>
                                        </svg>
                                        {{ __('Mengupload...') }}
                                    </div>

                                    @error('form.image')
                                        <p class="mt-0.5 text-[10px] text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            {{-- Baris 1: Name & Code --}}
                            <div class="grid grid-cols-1 gap-2.5 sm:grid-cols-2">
                                <div>
                                    <label
                                        class="mb-0.5 block text-[11px] font-medium text-stone-600 dark:text-stone-400">
                                        {{ __('Nama Toko') }}
                                    </label>
                                    <input wire:model="form.name" type="text" required
                                        class="w-full rounded-md border border-stone-200 bg-stone-50 px-2.5 py-1.5 text-xs text-stone-800 placeholder:text-stone-400 focus:border-sage-500 focus:outline-none focus:ring-1 focus:ring-sage-500 dark:border-stone-700 dark:bg-stone-800 dark:text-stone-100" />
                                    @error('form.name')
                                        <p class="mt-0.5 text-[10px] text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label
                                        class="mb-0.5 block text-[11px] font-medium text-stone-600 dark:text-stone-400">
                                        {{ __('Kode') }}
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
                                    {{ __('Lokasi') }}
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
                                    {{ __('Status') }}
                                </label>
                                <select wire:model="form.is_active" required
                                    class="w-full rounded-md border border-stone-200 bg-stone-50 px-2.5 py-1.5 text-xs text-stone-800 focus:border-sage-500 focus:outline-none focus:ring-1 focus:ring-sage-500 dark:border-stone-700 dark:bg-stone-800 dark:text-stone-100">
                                    <option value="">-- Pilih Status --</option>
                                    <option value="1">Aktif</option>
                                    <option value="0">Non Aktif</option>
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
