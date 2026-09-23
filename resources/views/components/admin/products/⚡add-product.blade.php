<?php

use Livewire\Component;
use App\Livewire\Forms\ProductForm;
use Illuminate\Validation\ValidationException;
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;
    public ProductForm $form;

    public function create()
    {
        try {
            $this->form->create();
            session()->flash('success', 'Product berhasil ditambahkan.');
            $this->dispatch('product-added', message: 'Product berhasil ditambahkan.');
        } catch (ValidationException $e) {
            session()->flash('error', 'Terjadi kesalahan saat membuat product: ' . $e->getMessage());
            $this->dispatch('product-error', message: 'Terjadi kesalahan saat membuat product: ' . $e->getMessage());
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
            window.addEventListener('add-product-modal', () => {
                this.errorMessage = '';
                this.show = true;
            });
            window.addEventListener('product-error', (e) => {
                this.errorMessage = e.detail.message;
            });
            window.addEventListener('product-added', (e) => {
                this.show = false;
                this.successMessage = e.detail.message;
            });
        }
    }" x-show="show" x-transition.opacity
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-3 text-xs sm:p-4" x-cloak
        @click.self="show = false">

        {{-- Container modal: flex-col supaya header/body/footer terpisah --}}
        <div class="flex max-h-[90vh] w-full max-w-lg flex-col overflow-hidden rounded-lg bg-white shadow-xl dark:bg-stone-900"
            @click.stop>

            <form wire:submit.prevent="create" class="flex min-h-0 flex-1 flex-col">

                {{-- HEADER (fixed) --}}
                <div class="flex-shrink-0 space-y-3 p-4 pb-3">
                    <div>
                        <h3 class="text-sm font-semibold text-stone-800 dark:text-stone-100">
                            {{ __('Add Product Baru') }}
                        </h3>
                        <p class="mt-0.5 text-[11px] leading-normal text-stone-500 dark:text-stone-400">
                            {{ __('Isi data detail product untuk product baru.') }}
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

                            {{-- Baris 1: Code & Name --}}
                            <div class="grid grid-cols-1 gap-2.5 sm:grid-cols-2">
                                <div>
                                    <label
                                        class="mb-0.5 block text-[11px] font-medium text-stone-600 dark:text-stone-400">
                                        {{ __('Code') }}
                                    </label>
                                    <input wire:model="form.code" type="text" maxlength="255" placeholder="PROD-001"
                                        required
                                        class="w-full rounded-md border border-stone-200 bg-stone-50 px-2.5 py-1.5 text-xs text-stone-800 placeholder:text-stone-400 focus:border-sage-500 focus:outline-none focus:ring-1 focus:ring-sage-500 dark:border-stone-700 dark:bg-stone-800 dark:text-stone-100" />
                                    @error('form.code')
                                        <p class="mt-0.5 text-[10px] text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label
                                        class="mb-0.5 block text-[11px] font-medium text-stone-600 dark:text-stone-400">
                                        {{ __('Product Name') }}
                                    </label>
                                    <input wire:model="form.name" type="text" required
                                        class="w-full rounded-md border border-stone-200 bg-stone-50 px-2.5 py-1.5 text-xs text-stone-800 placeholder:text-stone-400 focus:border-sage-500 focus:outline-none focus:ring-1 focus:ring-sage-500 dark:border-stone-700 dark:bg-stone-800 dark:text-stone-100" />
                                    @error('form.name')
                                        <p class="mt-0.5 text-[10px] text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            {{-- Baris 2: Description --}}
                            <div>
                                <label class="mb-0.5 block text-[11px] font-medium text-stone-600 dark:text-stone-400">
                                    {{ __('Description') }}
                                </label>
                                <textarea wire:model="form.description" rows="3" required placeholder="Deskripsi produk..."
                                    class="w-full resize-none rounded-md border border-stone-200 bg-stone-50 px-2.5 py-1.5 text-xs text-stone-800 placeholder:text-stone-400 focus:border-sage-500 focus:outline-none focus:ring-1 focus:ring-sage-500 dark:border-stone-700 dark:bg-stone-800 dark:text-stone-100"></textarea>
                                @error('form.description')
                                    <p class="mt-0.5 text-[10px] text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Baris 3: Image Upload dengan Preview --}}
                            <div>
                                <label class="mb-0.5 block text-[11px] font-medium text-stone-600 dark:text-stone-400">
                                    {{ __('Product Image') }}
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

                                    {{-- Area Upload --}}
                                    <div x-show="!previewUrl"
                                        class="flex flex-col items-center justify-center rounded-md border-2 border-dashed border-stone-200 bg-stone-50 px-3 py-4 text-center transition hover:border-sage-400 dark:border-stone-700 dark:bg-stone-800/50 dark:hover:border-sage-600">
                                        <svg class="mb-1.5 h-6 w-6 text-stone-400 dark:text-stone-500" fill="none"
                                            stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                        </svg>
                                        <label class="cursor-pointer">
                                            <span
                                                class="text-sage-600 dark:text-sage-400 text-[11px] font-medium hover:underline">
                                                {{ __('Klik untuk upload') }}
                                            </span>
                                            <span class="text-[11px] text-stone-500 dark:text-stone-400">
                                                {{ __(' atau drag & drop') }}
                                            </span>
                                            <input wire:model="form.image" type="file" accept="image/*"
                                                class="hidden" x-on:change="handleFile($event)" />
                                        </label>
                                        <p class="mt-1 text-[10px] text-stone-400 dark:text-stone-500">
                                            {{ __('PNG, JPG, WEBP maks 2MB') }}
                                        </p>
                                    </div>

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

                                    {{-- Indikator upload --}}
                                    <div wire:loading wire:target="form.image"
                                        class="mt-1 flex items-center gap-1.5 text-[10px] text-sage-600 dark:text-sage-400">
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

                            <div x-data="{
                                // ─── State per field ───
                                hpp: { display: '', raw: '' },
                                jual: { display: '', raw: '' },
                                diskon: { display: '', raw: '' },

                                // ─── Helper ───
                                normalize(val) {
                                    if (val === null || val === undefined || val === '') return '';
                                    return String(val).replace(/\D/g, '');
                                },
                                format(val) {
                                    if (!val || val === '') return '';
                                    return 'Rp ' + new Intl.NumberFormat('id-ID').format(val);
                                },

                                // ─── Sync dari Livewire ───
                                init() {
                                    this.syncFromLivewire('hpp', 'form.initial_price');
                                    this.syncFromLivewire('jual', 'form.price');
                                    this.syncFromLivewire('diskon', 'form.discount_price');
                                },

                                syncFromLivewire(field, livewirePath) {
                                    // Nilai awal
                                    const initial = this.normalize($wire.get(livewirePath));
                                    this[field].raw = initial;
                                    this[field].display = this.format(initial);

                                    // Watch perubahan dari Livewire
                                    this.$watch(() => $wire.get(livewirePath), (val) => {
                                        const normalized = this.normalize(val);
                                        if (normalized !== this[field].raw) {
                                            this[field].raw = normalized;
                                            this[field].display = this.format(normalized);
                                        }
                                    });
                                },

                                // ─── Handle input user ───
                                onInput(field, livewirePath, event) {
                                    const raw = this.normalize(event.target.value);
                                    this[field].raw = raw;
                                    this[field].display = this.format(raw);

                                    $wire.set(livewirePath, raw === '' ? '' : parseInt(raw, 10), false);

                                    // Jaga cursor di akhir
                                    this.$nextTick(() => {
                                        event.target.value = this[field].display;
                                        const len = event.target.value.length;
                                        event.target.setSelectionRange(len, len);
                                    });
                                }
                            }">

                                {{-- Baris: HPP & Harga Jual --}}
                                <div class="grid grid-cols-1 gap-2.5 sm:grid-cols-2">

                                    {{-- HPP --}}
                                    <div>
                                        <label
                                            class="mb-0.5 block text-[11px] font-medium text-stone-600 dark:text-stone-400">
                                            {{ __('HPP (Harga Pokok)') }}
                                        </label>
                                        <input type="text" inputmode="numeric" x-model="hpp.display"
                                            @input="onInput('hpp', 'form.initial_price', $event)" placeholder="Rp 0"
                                            required
                                            class="w-full rounded-md border border-stone-200 bg-stone-50 px-2.5 py-1.5 text-xs text-stone-800 placeholder:text-stone-400 focus:border-sage-500 focus:outline-none focus:ring-1 focus:ring-sage-500 dark:border-stone-700 dark:bg-stone-800 dark:text-stone-100" />
                                        <p class="mt-0.5 text-[10px] text-stone-400 dark:text-stone-500">
                                            {{ __('Modal / harga beli dari supplier.') }}
                                        </p>
                                        @error('form.initial_price')
                                            <p class="mt-0.5 text-[10px] text-red-500">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    {{-- Harga Jual --}}
                                    <div>
                                        <label
                                            class="mb-0.5 block text-[11px] font-medium text-stone-600 dark:text-stone-400">
                                            {{ __('Harga Jual') }}
                                        </label>
                                        <input type="text" inputmode="numeric" x-model="jual.display"
                                            @input="onInput('jual', 'form.price', $event)" placeholder="Rp 0" required
                                            class="w-full rounded-md border border-stone-200 bg-stone-50 px-2.5 py-1.5 text-xs text-stone-800 placeholder:text-stone-400 focus:border-sage-500 focus:outline-none focus:ring-1 focus:ring-sage-500 dark:border-stone-700 dark:bg-stone-800 dark:text-stone-100" />
                                        <p class="mt-0.5 text-[10px] text-stone-400 dark:text-stone-500">
                                            {{ __('Harga normal yang dibayar customer.') }}
                                        </p>
                                        @error('form.price')
                                            <p class="mt-0.5 text-[10px] text-red-500">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Baris: Diskon & Min Qty --}}
                                <div class="grid grid-cols-1 gap-2.5 sm:grid-cols-2">

                                    {{-- Harga Diskon --}}
                                    <div>
                                        <label
                                            class="mb-0.5 block text-[11px] font-medium text-stone-600 dark:text-stone-400">
                                            {{ __('Harga Diskon') }}
                                        </label>
                                        <input type="text" inputmode="numeric" x-model="diskon.display"
                                            @input="onInput('diskon', 'form.discount_price', $event)"
                                            placeholder="Rp 0"
                                            class="w-full rounded-md border border-stone-200 bg-stone-50 px-2.5 py-1.5 text-xs text-stone-800 placeholder:text-stone-400 focus:border-sage-500 focus:outline-none focus:ring-1 focus:ring-sage-500 dark:border-stone-700 dark:bg-stone-800 dark:text-stone-100" />
                                        <p class="mt-0.5 text-[10px] text-stone-400 dark:text-stone-500">
                                            {{ __('Harga promo untuk pembelian grosir.') }}
                                        </p>
                                        @error('form.discount_price')
                                            <p class="mt-0.5 text-[10px] text-red-500">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    {{-- Min Qty (tidak berubah) --}}
                                    <div>
                                        <label
                                            class="mb-0.5 block text-[11px] font-medium text-stone-600 dark:text-stone-400">
                                            {{ __('Min. Qty untuk Diskon') }}
                                        </label>
                                        <input wire:model="form.minimal_discount" type="number" min="1"
                                            placeholder="cth: 10"
                                            class="w-full rounded-md border border-stone-200 bg-stone-50 px-2.5 py-1.5 text-xs text-stone-800 placeholder:text-stone-400 focus:border-sage-500 focus:outline-none focus:ring-1 focus:ring-sage-500 dark:border-stone-700 dark:bg-stone-800 dark:text-stone-100" />
                                        <p class="mt-0.5 text-[10px] text-stone-400 dark:text-stone-500">
                                            {{ __('Kuantitas minimum agar harga diskon berlaku.') }}
                                        </p>
                                        @error('form.minimal_discount')
                                            <p class="mt-0.5 text-[10px] text-red-500">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                            </div>

                            {{-- Baris 6: Active --}}
                            <div>
                                <label class="mb-0.5 block text-[11px] font-medium text-stone-600 dark:text-stone-400">
                                    {{ __('Status') }}
                                </label>

                                <div
                                    class="flex items-center gap-2.5 rounded-md border border-stone-200 bg-stone-50 px-2.5 py-2 dark:border-stone-700 dark:bg-stone-800">
                                    {{-- Toggle --}}
                                    <button type="button" wire:click="$toggle('form.is_active')" role="switch"
                                        aria-checked="{{ $form->is_active ? 'true' : 'false' }}"
                                        class="relative inline-flex h-5 w-9 shrink-0 cursor-pointer items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-sage-500 focus:ring-offset-1
                {{ $form->is_active ? 'bg-sage-600 dark:bg-sage-500' : 'bg-stone-300 dark:bg-stone-600' }}">
                                        <span
                                            class="inline-block h-3.5 w-3.5 transform rounded-full bg-white shadow transition-transform
                    {{ $form->is_active ? 'translate-x-[18px]' : 'translate-x-0.5' }}">
                                        </span>
                                    </button>

                                    {{-- Label Status --}}
                                    <div class="flex flex-col">
                                        <span
                                            class="text-[11px] font-medium {{ $form->is_active ? 'text-sage-700 dark:text-sage-400' : 'text-stone-500 dark:text-stone-400' }}">
                                            {{ $form->is_active ? __('Active') : __('Inactive') }}
                                        </span>
                                        <span class="text-[10px] text-stone-400 dark:text-stone-500">
                                            {{ $form->is_active ? __('Produk tampil di katalog.') : __('Produk disembunyikan.') }}
                                        </span>
                                    </div>
                                </div>

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
