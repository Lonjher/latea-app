<?php

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Flux\Flux;

new class extends Component {
    public ?int $cashierId = null;
    public string $cashierName = '';
    public string $cashierEmail = '';
    public ?string $generatedPassword = null;

    /**
     * Listener: buka modal & isi data kasir.
     */
    public function open(int $id): void
    {
        $cashier = User::findOrFail($id);

        $this->cashierId = $cashier->id;
        $this->cashierName = $cashier->name;
        $this->cashierEmail = $cashier->email ?? '—';
        $this->generatedPassword = null;

        $this->dispatch('reset-password-opened');
    }

    /**
     * Generate & simpan password baru.
     */
    public function resetPassword(): void
    {
        if (!$this->cashierId) {
            return;
        }

        $user = User::findOrFail($this->cashierId);

        $newPassword = $this->generateReadablePassword();

        $user->update([
            'password' => Hash::make($newPassword),
            // 'must_change_password' => true,
        ]);

        $this->generatedPassword = $newPassword;

        Flux::toast(variant: 'success', text: __('Password berhasil direset untuk :name.', ['name' => $user->name]));
    }

    /**
     * Reset state saat modal ditutup.
     */
    public function close(): void
    {
        $this->cashierId = null;
        $this->cashierName = '';
        $this->cashierEmail = '';
        $this->generatedPassword = null;
    }

    /**
     * Buat password acak yang mudah dibaca & diketik.
     */
    protected function generateReadablePassword(): string
    {
        $words = ['Sinar', 'Bumi', 'Langit', 'Samudra', 'Hutan', 'Bintang', 'Mentari', 'Angkasa', 'Purnama', 'Cahaya'];
        $word = $words[array_rand($words)];
        $number = str_pad((string) random_int(1000, 9999), 4, '0', STR_PAD_LEFT);

        return $word . $number;
    }
};
?>

<div>
    <div x-data="{
        show: false,
        init() {
            window.addEventListener('reset-password-modal', (e) => {
                $wire.open(e.detail.id);
                this.show = true;
            });
            window.addEventListener('reset-password-opened', () => {
                this.show = true;
            });
        },
        close() {
            this.show = false;
            $wire.close();
        }
    }" x-show="show" x-transition.opacity
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-3 text-xs sm:p-4" x-cloak
        @click.self="close()" @keydown.escape.window="show && close()">

        <div class="flex max-h-[90vh] w-full max-w-md flex-col overflow-hidden rounded-lg bg-white shadow-xl dark:bg-stone-900"
            @click.stop>

            {{-- HEADER --}}
            <div class="flex-shrink-0 space-y-3 p-4 pb-3">
                <div class="flex items-start gap-3">
                    <div
                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full
                            {{ $generatedPassword ? 'bg-green-100 dark:bg-green-950/50' : 'bg-amber-100 dark:bg-amber-950/50' }}">
                        @if ($generatedPassword)
                            <svg class="h-4.5 w-4.5 text-green-600 dark:text-green-400" fill="none"
                                stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        @else
                            <svg class="h-4.5 w-4.5 text-amber-600 dark:text-amber-400" fill="none"
                                stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                            </svg>
                        @endif
                    </div>
                    <div class="flex-1">
                        <h3 class="text-sm font-semibold text-stone-800 dark:text-stone-100">
                            {{ $generatedPassword ? __('Password Baru') : __('Reset Password?') }}
                        </h3>
                        <p class="mt-0.5 text-[11px] leading-normal text-stone-500 dark:text-stone-400">
                            @if ($generatedPassword)
                                {{ __('Catat password ini sekarang. Tidak akan bisa dilihat lagi setelah modal ditutup.') }}
                            @else
                                {{ __('Password akun :name akan diganti dengan password acak baru.', ['name' => $cashierName]) }}
                            @endif
                        </p>
                    </div>
                </div>
                <div class="border-t border-stone-100 dark:border-stone-800"></div>
            </div>

            {{-- BODY --}}
            <div class="min-h-0 flex-1 overflow-y-auto px-4">
                <div class="relative">
                    {{-- Loading overlay --}}
                    <div wire:loading wire:target="resetPassword"
                        class="absolute inset-0 z-50 flex items-center justify-center rounded-md bg-white/60 backdrop-blur-[0.5px] dark:bg-stone-900/60">
                        <div
                            class="flex items-center gap-1.5 rounded-md border border-stone-100 bg-white px-2.5 py-1.5 shadow-sm dark:border-stone-700 dark:bg-stone-800">
                            <svg class="text-sage-600 dark:text-sage-400 h-3.5 w-3.5 animate-spin" fill="none"
                                viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V12H4z"></path>
                            </svg>
                            <span class="text-[10px] font-medium text-stone-600 dark:text-stone-300">
                                {{ __('Mereset password...') }}
                            </span>
                        </div>
                    </div>

                    <div class="space-y-3 pb-4">

                        @if (!$generatedPassword)
                            {{-- ═══ STEP 1: Konfirmasi ═══ --}}
                            <div
                                class="rounded-md border border-stone-200 bg-stone-50 px-3 py-2.5 dark:border-stone-700 dark:bg-stone-800">
                                <p class="text-[11px] font-medium text-stone-800 dark:text-stone-200">
                                    {{ $cashierName }}
                                </p>
                                <p class="mt-0.5 text-[10px] text-stone-500 dark:text-stone-400">
                                    {{ $cashierEmail }}
                                </p>
                            </div>

                            <div
                                class="rounded-md border border-amber-200 bg-amber-50 px-3 py-2 text-[11px] text-amber-800 dark:border-amber-900/50 dark:bg-amber-950/30 dark:text-amber-300">
                                {{ __('Pastikan kasir sudah siap menerima password baru sebelum melanjutkan.') }}
                            </div>
                        @else
                            {{-- ═══ STEP 2: Tampilkan password ═══ --}}
                            <div x-data="{
                                copied: false,
                                copy() {
                                    navigator.clipboard.writeText('{{ $generatedPassword }}');
                                    this.copied = true;
                                    setTimeout(() => this.copied = false, 2000);
                                }
                            }"
                                class="flex items-center gap-2 rounded-md border border-stone-200 bg-stone-50 px-3 py-2.5 dark:border-stone-700 dark:bg-stone-800">
                                <code
                                    class="flex-1 font-mono text-base font-semibold tracking-wider text-stone-800 dark:text-stone-100">
                                    {{ $generatedPassword }}
                                </code>
                                <button type="button" @click="copy()"
                                    class="inline-flex items-center gap-1.5 rounded-md border border-stone-200 bg-white px-2.5 py-1 text-[11px] font-medium text-stone-700 transition hover:bg-stone-50 dark:border-stone-700 dark:bg-stone-900 dark:text-stone-300 dark:hover:bg-stone-700">
                                    <template x-if="!copied">
                                        <span class="inline-flex items-center gap-1.5">
                                            <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 01-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 011.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 00-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 01-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 00-3.375-3.375h-1.5a1.125 1.125 0 01-1.125-1.125v-1.5a3.375 3.375 0 00-3.375-3.375H9.75" />
                                            </svg>
                                            {{ __('Copy') }}
                                        </span>
                                    </template>
                                    <template x-if="copied">
                                        <span
                                            class="inline-flex items-center gap-1.5 text-green-600 dark:text-green-400">
                                            <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M4.5 12.75l6 6 9-13.5" />
                                            </svg>
                                            {{ __('Copied!') }}
                                        </span>
                                    </template>
                                </button>
                            </div>

                            <div
                                class="rounded-md border border-amber-200 bg-amber-50 px-3 py-2 text-[11px] text-amber-800 dark:border-amber-900/50 dark:bg-amber-950/30 dark:text-amber-300">
                                {{ __('Berikan password ini ke kasir dan minta untuk segera menggantinya setelah login.') }}
                            </div>
                        @endif

                    </div>
                </div>
            </div>

            {{-- FOOTER --}}
            <div
                class="flex flex-shrink-0 justify-end gap-1.5 border-t border-stone-100 bg-white p-4 pt-3 dark:border-stone-800 dark:bg-stone-900">
                @if (!$generatedPassword)
                    <button type="button" @click="close()"
                        class="rounded-md border border-stone-200 px-3 py-1.5 text-xs font-medium text-stone-600 transition hover:bg-stone-50 dark:border-stone-700 dark:text-stone-400 dark:hover:bg-stone-800">
                        {{ __('Batal') }}
                    </button>

                    <button type="button" wire:click="resetPassword" wire:loading.attr="disabled" wire:target="resetPassword"
                        class="bg-sage-600 hover:bg-sage-700 focus:ring-sage-500 dark:bg-sage-500 dark:hover:bg-sage-600 rounded-md px-3 py-1.5 text-xs font-medium text-white transition focus:outline-none focus:ring-2 focus:ring-offset-1 disabled:cursor-not-allowed disabled:opacity-60">
                        {{ __('Ya, Reset Sekarang') }}
                    </button>
                @else
                    <button type="button" @click="close()"
                        class="bg-sage-600 hover:bg-sage-700 focus:ring-sage-500 dark:bg-sage-500 dark:hover:bg-sage-600 rounded-md px-3 py-1.5 text-xs font-medium text-white transition focus:outline-none focus:ring-2 focus:ring-offset-1">
                        {{ __('Selesai') }}
                    </button>
                @endif
            </div>

        </div>
    </div>
</div>
