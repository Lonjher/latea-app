<?php

use Livewire\Component;
use Livewire\Attributes\Locked;
use App\Models\StoreProduct;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Validation\ValidationException;

new class extends Component {
    #[Locked]
    public int $storeId;

    public ?int $productId = null;
    public $price = null;
    public bool $isAvailable = true;
    public ?string $productName = null;
    public $defaultPrice = null;

    public function mount(int $storeId)
    {
        $this->storeId = $storeId;
    }

    public function editStoreProduct(int $productId)
    {
        $product = Product::findOrFail($productId);
        $pivot = StoreProduct::where('store_id', $this->storeId)->where('product_id', $productId)->firstOrFail();

        $this->productId = $productId;
        $this->productName = $product->name;
        $this->defaultPrice = $product->price;
        $this->price = $pivot->price;
        $this->isAvailable = (bool) $pivot->is_available;
    }

    public function updateStoreProduct()
    {
        try {
            $this->validate([
                'price' => ['nullable', 'numeric', 'min:0'],
                'isAvailable' => ['boolean'],
            ]);

            StoreProduct::where('store_id', $this->storeId)
                ->where('product_id', $this->productId)
                ->update([
                    'price' => $this->price === '' ? null : $this->price,
                    'is_available' => $this->isAvailable,
                ]);

            session()->flash('success', 'Harga produk berhasil diperbarui.');
            $this->dispatch('edit-success', message: 'Harga produk berhasil diperbarui.');
        } catch (ValidationException $e) {
            $this->dispatch('edit-error', message: 'Terjadi kesalahan: ' . $e->getMessage());
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
            window.addEventListener('edit-store-product-modal', (e) => {
                $wire.editStoreProduct(e.detail.productId);
                this.errorMessage = '';
                this.show = true;
            });
            window.addEventListener('edit-success', (e) => {
                this.show = false;
                this.successMessage = e.detail.message;
            });
            window.addEventListener('edit-error', (e) => {
                this.errorMessage = e.detail.message;
            });
        }
    }" x-show="show" x-transition.opacity
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-3 text-xs sm:p-4" x-cloak
        @click.self="show = false">

        <div class="flex max-h-[90vh] w-full max-w-md flex-col overflow-hidden rounded-lg bg-white shadow-xl dark:bg-stone-900"
            @click.stop>

            <form wire:submit.prevent="updateStoreProduct" class="flex min-h-0 flex-1 flex-col">

                {{-- HEADER --}}
                <div class="flex-shrink-0 space-y-3 p-4 pb-3">
                    <div>
                        <h3 class="text-sm font-semibold text-stone-800 dark:text-stone-100">
                            {{ __('Edit Harga Produk') }}
                        </h3>
                        <p class="mt-0.5 text-[11px] leading-normal text-stone-500 dark:text-stone-400">
                            {{ $productName }}
                        </p>
                    </div>
                    <div class="border-t border-stone-100 dark:border-stone-800"></div>
                </div>

                {{-- BODY --}}
                <div class="min-h-0 flex-1 overflow-y-auto px-4">
                    <div x-show="errorMessage" x-cloak
                        class="mb-2 rounded-md border border-red-200 bg-red-50 px-2.5 py-1.5 text-[11px] text-red-700 dark:border-red-900/50 dark:bg-red-950/30 dark:text-red-300"
                        x-text="errorMessage"></div>

                    <div class="space-y-2.5 pb-4">

                        {{-- Info Harga Default --}}
                        <div
                            class="rounded-md border border-stone-200 bg-stone-50 px-2.5 py-1.5 dark:border-stone-700 dark:bg-stone-800/50">
                            <div class="flex items-center justify-between text-[11px]">
                                <span
                                    class="text-stone-500 dark:text-stone-400">{{ __('Harga default produk:') }}</span>
                                <span class="font-mono font-medium text-stone-700 dark:text-stone-300">
                                    {{ $defaultPrice }}
                                </span>
                            </div>
                        </div>

                        {{-- Harga Khusus Toko --}}
                        <div x-data="{
                            raw: '',
                            display: '',
                            init() {
                                this.raw = this.normalize($wire.get('price'));
                                this.display = this.format(this.raw);
                                this.$watch(() => $wire.get('price'), (val) => {
                                    const n = this.normalize(val);
                                    if (n !== this.raw) {
                                        this.raw = n;
                                        this.display = this.format(n);
                                    }
                                });
                            },
                            normalize(val) {
                                if (val === null || val === undefined || val === '') return '';
                                let str = String(val);
                                if (str.includes('.')) str = str.split('.')[0];
                                if (str.includes(',')) str = str.split(',')[0];
                                return str.replace(/[^0-9]/g, '');
                            },
                            format(val) {
                                if (!val || val === '') return '';
                                return 'Rp ' + new Intl.NumberFormat('id-ID').format(parseInt(val, 10));
                            },
                            onInput(e) {
                                const raw = this.normalize(e.target.value);
                                this.raw = raw;
                                this.display = this.format(raw);
                                $wire.set('price', raw === '' ? null : parseInt(raw, 10), false);
                                this.$nextTick(() => {
                                    e.target.value = this.display;
                                    const len = e.target.value.length;
                                    e.target.setSelectionRange(len, len);
                                });
                            }
                        }">
                            <label class="mb-0.5 block text-[11px] font-medium text-stone-600 dark:text-stone-400">
                                {{ __('Harga Khusus Toko Ini') }}
                            </label>
                            <input type="text" inputmode="numeric" x-model="display" @input="onInput($event)"
                                placeholder="Rp 0 — kosongkan untuk pakai harga default"
                                class="w-full rounded-md border border-stone-200 bg-stone-50 px-2.5 py-1.5 text-xs text-stone-800 placeholder:text-stone-400 focus:border-sage-500 focus:outline-none focus:ring-1 focus:ring-sage-500 dark:border-stone-700 dark:bg-stone-800 dark:text-stone-100" />
                            <p class="mt-0.5 text-[10px] text-stone-400 dark:text-stone-500">
                                {{ __('Kalau kosong, harga mengikuti harga default produk.') }}
                            </p>
                            @error('price')
                                <p class="mt-0.5 text-[10px] text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Status Ketersediaan --}}
                        <div>
                            <label class="mb-0.5 block text-[11px] font-medium text-stone-600 dark:text-stone-400">
                                {{ __('Ketersediaan') }}
                            </label>
                            <div
                                class="flex items-center gap-2.5 rounded-md border border-stone-200 bg-stone-50 px-2.5 py-2 dark:border-stone-700 dark:bg-stone-800">
                                <button type="button" wire:click="$toggle('isAvailable')" role="switch"
                                    class="relative inline-flex h-5 w-9 shrink-0 cursor-pointer items-center rounded-full transition-colors
                                        {{ $isAvailable ? 'bg-sage-600 dark:bg-sage-500' : 'bg-stone-300 dark:bg-stone-600' }}">
                                    <span
                                        class="inline-block h-3.5 w-3.5 transform rounded-full bg-white shadow transition-transform
                                        {{ $isAvailable ? 'translate-x-[18px]' : 'translate-x-0.5' }}"></span>
                                </button>
                                <div class="flex flex-col">
                                    <span
                                        class="text-[11px] font-medium {{ $isAvailable ? 'text-sage-700 dark:text-sage-400' : 'text-stone-500 dark:text-stone-400' }}">
                                        {{ $isAvailable ? __('Tersedia') : __('Habis') }}
                                    </span>
                                    <span class="text-[10px] text-stone-400 dark:text-stone-500">
                                        {{ $isAvailable ? __('Produk bisa dibeli di toko ini.') : __('Produk tidak bisa dibeli sementara.') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- FOOTER --}}
                <div
                    class="flex flex-shrink-0 justify-end gap-1.5 border-t border-stone-100 bg-white p-4 pt-3 dark:border-stone-800 dark:bg-stone-900">
                    <button type="button" @click="show = false"
                        class="cursor-pointer rounded-md border border-stone-200 px-3 py-1.5 text-xs font-medium text-stone-600 transition hover:bg-stone-50 dark:border-stone-700 dark:text-stone-400 dark:hover:bg-stone-800">
                        {{ __('Batal') }}
                    </button>

                    <button type="submit" wire:loading.attr="disabled" wire:target="updateStoreProduct"
                        class="bg-sage-600 hover:bg-sage-700 focus:ring-sage-500 dark:bg-sage-500 dark:hover:bg-sage-600 cursor-pointer rounded-md px-3 py-1.5 text-xs font-medium text-white transition focus:outline-none focus:ring-2 focus:ring-offset-1 disabled:cursor-not-allowed disabled:opacity-60">
                        {{ __('Perbarui') }}
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>
