<?php

use Livewire\Component;
use Livewire\Attributes\Locked;
use App\Models\Store;
use App\Models\Product;
use App\Models\StoreProduct;

new class extends Component {
    #[Locked]
    public int $storeId;

    public $search;
    public array $selectedProducts = [];
    public $defaultPrice = null;
    public bool $defaultAvailable = true;

    public function mount(int $storeId)
    {
        $this->storeId = $storeId;
    }

    public function toggleProduct(int $productId): void
    {
        if (in_array($productId, $this->selectedProducts)) {
            $this->selectedProducts = array_values(array_diff($this->selectedProducts, [$productId]));
        } else {
            $this->selectedProducts[] = $productId;
        }
    }

    public function attachAll()
    {
        if (empty($this->selectedProducts)) {
            $this->dispatch('attach-error', message: 'Pilih minimal satu produk.');
            return;
        }

        $data = [];
        foreach ($this->selectedProducts as $productId) {
            $data[$productId] = [
                'price' => $this->defaultPrice,
                'is_available' => $this->defaultAvailable,
            ];
        }

        Store::findOrFail($this->storeId)
            ->products()
            ->syncWithoutDetaching($data);

        $count = count($this->selectedProducts);
        $this->reset(['selectedProducts', 'search', 'defaultPrice']);
        $this->defaultAvailable = true;

        $this->dispatch('product-attached', message: "$count produk berhasil ditambahkan.");
    }

    public function with()
    {
        // Produk yang BELUM di-attach ke toko ini
        $availableProducts = Product::query()
            ->whereDoesntHave('stores', fn ($q) => $q->where('store_id', $this->storeId))
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('code', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy('name')
            ->limit(50)
            ->get();

        return [
            'availableProducts' => $availableProducts,
        ];
    }
};
?>

<div>
    <div x-data="{
        show: false,
        errorMessage: '',
        successMessage: '',
        init() {
            window.addEventListener('attach-product-modal', () => {
                this.errorMessage = '';
                this.show = true;
            });
            window.addEventListener('attach-error', (e) => {
                this.errorMessage = e.detail.message;
            });
            window.addEventListener('product-attached', (e) => {
                this.show = false;
                this.successMessage = e.detail.message;
            });
        }
    }" x-show="show" x-transition.opacity
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-3 text-xs sm:p-4" x-cloak
        @click.self="show = false">

        <div class="flex max-h-[90vh] w-full max-w-lg flex-col overflow-hidden rounded-lg bg-white shadow-xl dark:bg-stone-900"
            @click.stop>

            <form wire:submit.prevent="attachAll" class="flex min-h-0 flex-1 flex-col">

                {{-- HEADER --}}
                <div class="flex-shrink-0 space-y-3 p-4 pb-3">
                    <div>
                        <h3 class="text-sm font-semibold text-stone-800 dark:text-stone-100">
                            {{ __('Attach Products ke Toko') }}
                        </h3>
                        <p class="mt-0.5 text-[11px] leading-normal text-stone-500 dark:text-stone-400">
                            {{ __('Pilih produk yang akan dijual di toko ini.') }}
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

                        {{-- Search --}}
                        <div class="relative">
                            <svg class="pointer-events-none absolute left-2.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-stone-400"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z" />
                            </svg>
                            <input wire:model.live.debounce.300ms="search" type="text"
                                placeholder="Cari produk…"
                                class="w-full rounded-md border border-stone-200 bg-stone-50 py-1.5 pl-8 pr-2.5 text-xs text-stone-800 placeholder:text-stone-400 focus:border-sage-500 focus:outline-none focus:ring-1 focus:ring-sage-500 dark:border-stone-700 dark:bg-stone-800 dark:text-stone-100" />
                        </div>

                        {{-- Settings default --}}
                        <div class="space-y-2 rounded-md border border-stone-200 bg-stone-50 p-2.5 dark:border-stone-700 dark:bg-stone-800/50">
                            <p class="text-[10px] font-semibold uppercase tracking-wider text-stone-500 dark:text-stone-400">
                                {{ __('Default (bisa diubah per produk nanti)') }}
                            </p>

                            <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                                <div>
                                    <label class="mb-0.5 block text-[10px] font-medium text-stone-600 dark:text-stone-400">
                                        {{ __('Harga Khusus Toko') }}
                                    </label>
                                    <input wire:model="defaultPrice" type="number" step="0.01" min="0"
                                        placeholder="Kosong = pakai default"
                                        class="w-full rounded-md border border-stone-200 bg-white px-2 py-1 text-xs text-stone-800 placeholder:text-stone-400 focus:border-sage-500 focus:outline-none focus:ring-1 focus:ring-sage-500 dark:border-stone-700 dark:bg-stone-800 dark:text-stone-100" />
                                </div>

                                <div>
                                    <label class="mb-0.5 block text-[10px] font-medium text-stone-600 dark:text-stone-400">
                                        {{ __('Status') }}
                                    </label>
                                    <div class="flex items-center gap-2 rounded-md border border-stone-200 bg-white px-2 py-1 dark:border-stone-700 dark:bg-stone-800">
                                        <button type="button" wire:click="$toggle('defaultAvailable')" role="switch"
                                            class="relative inline-flex h-4 w-7 shrink-0 cursor-pointer items-center rounded-full transition-colors
                                                {{ $defaultAvailable ? 'bg-sage-600 dark:bg-sage-500' : 'bg-stone-300 dark:bg-stone-600' }}">
                                            <span class="inline-block h-3 w-3 transform rounded-full bg-white shadow transition-transform
                                                {{ $defaultAvailable ? 'translate-x-3.5' : 'translate-x-0.5' }}"></span>
                                        </button>
                                        <span class="text-[10px] font-medium {{ $defaultAvailable ? 'text-sage-700 dark:text-sage-400' : 'text-stone-500 dark:text-stone-400' }}">
                                            {{ $defaultAvailable ? __('Tersedia') : __('Habis') }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Product List --}}
                        <div class="space-y-1">
                            <p class="text-[10px] font-semibold uppercase tracking-wider text-stone-500 dark:text-stone-400">
                                {{ __('Pilih Produk') }} ({{ count($selectedProducts) }} {{ __('dipilih') }})
                            </p>

                            @if ($availableProducts->isEmpty())
                                <div class="rounded-md border border-dashed border-stone-200 py-6 text-center text-[11px] text-stone-400 dark:border-stone-700">
                                    {{ __('Semua produk sudah ada di toko ini.') }}
                                </div>
                            @else
                                <div class="max-h-64 space-y-1 overflow-y-auto rounded-md border border-stone-200 p-1.5 dark:border-stone-700">
                                    @foreach ($availableProducts as $product)
                                        <label class="flex cursor-pointer items-center gap-2 rounded-md px-2 py-1.5 transition hover:bg-stone-50 dark:hover:bg-stone-800/50
                                            {{ in_array($product->id, $selectedProducts) ? 'bg-sage-50 dark:bg-sage-950/30' : '' }}">
                                            <input type="checkbox"
                                                wire:click="toggleProduct({{ $product->id }})"
                                                @checked(in_array($product->id, $selectedProducts))
                                                class="h-3.5 w-3.5 cursor-pointer rounded border-stone-300 text-sage-600 focus:ring-sage-500 dark:border-stone-600 dark:bg-stone-700" />
                                            <div class="min-w-0 flex-1">
                                                <div class="truncate text-[11px] font-medium text-stone-800 dark:text-stone-200">
                                                    {{ $product->name }}
                                                </div>
                                                <div class="truncate text-[10px] text-stone-500 dark:text-stone-400">
                                                    {{ $product->code }} · {{ $product->price }}
                                                </div>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- FOOTER --}}
                <div class="flex flex-shrink-0 justify-end gap-1.5 border-t border-stone-100 bg-white p-4 pt-3 dark:border-stone-800 dark:bg-stone-900">
                    <button type="button" @click="show = false"
                        class="rounded-md border border-stone-200 px-3 py-1.5 text-xs font-medium text-stone-600 transition hover:bg-stone-50 dark:border-stone-700 dark:text-stone-400 dark:hover:bg-stone-800">
                        {{ __('Batal') }}
                    </button>

                    <button type="submit" wire:loading.attr="disabled" wire:target="attachAll"
                        class="rounded-md bg-sage-600 px-3 py-1.5 text-xs font-medium text-white transition hover:bg-sage-700 focus:outline-none focus:ring-2 focus:ring-sage-500 focus:ring-offset-1 disabled:cursor-not-allowed disabled:opacity-60 dark:bg-sage-500 dark:hover:bg-sage-600">
                        <span wire:loading.remove wire:target="attachAll">
                            {{ __('Attach') }} ({{ count($selectedProducts) }})
                        </span>
                        <span wire:loading wire:target="attachAll">{{ __('Menyimpan...') }}</span>
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>
