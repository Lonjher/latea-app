<?php

use Livewire\Component;
use App\Livewire\Forms\OperationalForm;
use App\Models\OperationalCost;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

new class extends Component {
    public OperationalForm $form;

    public function editOperational(OperationalCost $operational)
    {

        // ⭐ Guard: hanya bisa edit operational toko sendiri
        if ((int) $operational->store_id !== (int) Auth::user()->store_id) {
            abort(403, 'Anda tidak memiliki akses ke item ini.');
        }

        $this->form->setOperational($operational);
    }

    public function updateOperational()
    {
        try {
            $this->form->update();
            session()->flash('success', 'Item berhasil diperbarui.');
            $this->dispatch('operational-updated', message: 'Item berhasil diperbarui.');
            $this->dispatch('update-success', message: 'Item berhasil diperbarui.');
        } catch (ValidationException $e) {
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
            $this->dispatch('operational-error', message: 'Terjadi kesalahan: ' . $e->getMessage());
            $this->dispatch('update-error', message: 'Terjadi kesalahan: ' . $e->getMessage());
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
            window.addEventListener('edit-operational-modal', (e) => {
                $wire.editOperational(e.detail.operationalId);
                this.successMessage = '';
                this.errorMessage = '';
                this.show = true;
            });
            window.addEventListener('operational-error', (e) => {
                this.errorMessage = e.detail.message;
            });
            window.addEventListener('update-success', (e) => {
                this.show = false;
                this.successMessage = e.detail.message;
            });
        }
    }" x-show="show" x-transition.opacity
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-3 text-xs sm:p-4" x-cloak
        @click.self="show = false">

        <div class="flex max-h-[90vh] w-full max-w-md flex-col overflow-hidden rounded-lg bg-white shadow-xl dark:bg-stone-900"
            @click.stop>

            {{-- Loading fetch data --}}
            <div wire:loading wire:target="editOperational"
                class="absolute inset-0 z-50 flex items-center justify-center bg-white/60 backdrop-blur-[0.5px] dark:bg-stone-900/60">
                <div class="flex items-center gap-1.5 rounded-md border border-stone-100 bg-white px-2.5 py-1.5 shadow-sm dark:border-stone-700 dark:bg-stone-800">
                    <svg class="h-3.5 w-3.5 animate-spin text-sage-600 dark:text-sage-400" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V12H4z"></path>
                    </svg>
                    <span class="text-[10px] font-medium text-stone-600 dark:text-stone-300">
                        {{ __('Memuat data...') }}
                    </span>
                </div>
            </div>

            <form wire:submit.prevent="updateOperational" class="flex min-h-0 flex-1 flex-col">

                {{-- HEADER --}}
                <div class="flex-shrink-0 space-y-3 p-4 pb-3">
                    <div>
                        <h3 class="text-sm font-semibold text-stone-800 dark:text-stone-100">
                            {{ __('Edit Item Operational') }}
                        </h3>
                        <p class="mt-0.5 text-[11px] leading-normal text-stone-500 dark:text-stone-400">
                            {{ __('Perbarui detail biaya operasional toko Anda.') }}
                        </p>
                    </div>
                    <div class="border-t border-stone-100 dark:border-stone-800"></div>
                </div>

                {{-- BODY --}}
                <div class="min-h-0 flex-1 overflow-y-auto px-4">
                    {{-- Global Error --}}
                    <div x-show="errorMessage" x-cloak
                        class="mb-2 rounded-md border border-red-200 bg-red-50 px-2.5 py-1.5 text-[11px] text-red-700 dark:border-red-900/50 dark:bg-red-950/30 dark:text-red-300"
                        x-text="errorMessage">
                    </div>
                    <div x-show="successMessage" x-cloak
                        class="mb-2 rounded-md border border-green-200 bg-green-50 px-2.5 py-1.5 text-[11px] text-green-700 dark:border-green-900/50 dark:bg-green-950/30 dark:text-green-300"
                        x-text="successMessage">
                    </div>

                    <div class="relative">
                        {{-- Loading save --}}
                        <div wire:loading wire:target="updateOperational"
                            class="absolute inset-0 z-50 flex items-center justify-center rounded-md bg-white/60 backdrop-blur-[0.5px] dark:bg-stone-900/60">
                            <div class="flex items-center gap-1.5 rounded-md border border-stone-100 bg-white px-2.5 py-1.5 shadow-sm dark:border-stone-700 dark:bg-stone-800">
                                <svg class="h-3.5 w-3.5 animate-spin text-sage-600 dark:text-sage-400" fill="none"
                                    viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V12H4z"></path>
                                </svg>
                                <span class="text-[10px] font-medium text-stone-600 dark:text-stone-300">
                                    {{ __('Menyimpan data...') }}
                                </span>
                            </div>
                        </div>

                        <div class="space-y-2.5 pb-4">

                            {{-- Kegiatan --}}
                            <div>
                                <label class="mb-0.5 block text-[11px] font-medium text-stone-600 dark:text-stone-400">
                                    {{ __('Kegiatan / Nama Biaya') }}
                                </label>
                                <textarea wire:model="form.operational_name" rows="3" required
                                    placeholder="cth: Listrik bulan September, Sewa toko, Gaji kasir"
                                    class="w-full resize-none rounded-md border border-stone-200 bg-stone-50 px-2.5 py-1.5 text-xs text-stone-800 placeholder:text-stone-400 focus:border-sage-500 focus:outline-none focus:ring-1 focus:ring-sage-500 dark:border-stone-700 dark:bg-stone-800 dark:text-stone-100"></textarea>
                                @error('form.operational_name')
                                    <p class="mt-0.5 text-[10px] text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Biaya — Rp format --}}
                            <div x-data="{
                                display: '',
                                raw: '',
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
                                init() {
                                    this.raw = this.normalize($wire.get('form.cost'));
                                    this.display = this.format(this.raw);
                                    this.$watch(() => $wire.get('form.cost'), (val) => {
                                        const n = this.normalize(val);
                                        if (n !== this.raw) {
                                            this.raw = n;
                                            this.display = this.format(n);
                                        }
                                    });
                                },
                                onInput(e) {
                                    const raw = this.normalize(e.target.value);
                                    this.raw = raw;
                                    this.display = this.format(raw);
                                    $wire.set('form.cost', raw === '' ? '' : parseInt(raw, 10), false);
                                    this.$nextTick(() => {
                                        e.target.value = this.display;
                                        const len = e.target.value.length;
                                        e.target.setSelectionRange(len, len);
                                    });
                                }
                            }">
                                <label class="mb-0.5 block text-[11px] font-medium text-stone-600 dark:text-stone-400">
                                    {{ __('Biaya') }}
                                </label>
                                <input type="text" inputmode="numeric" x-model="display"
                                    @input="onInput($event)" placeholder="Rp 0"
                                    class="w-full rounded-md border border-stone-200 bg-stone-50 px-2.5 py-1.5 text-xs text-stone-800 placeholder:text-stone-400 focus:border-sage-500 focus:outline-none focus:ring-1 focus:ring-sage-500 dark:border-stone-700 dark:bg-stone-800 dark:text-stone-100" />
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

                    <button type="submit" wire:loading.attr="disabled" wire:target="updateOperational"
                        class="cursor-pointer rounded-md bg-sage-600 px-3 py-1.5 text-xs font-medium text-white transition hover:bg-sage-700 focus:outline-none focus:ring-2 focus:ring-sage-500 focus:ring-offset-1 disabled:cursor-not-allowed disabled:opacity-60 dark:bg-sage-500 dark:hover:bg-sage-600">
                        {{ __('Perbarui Data') }}
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>
