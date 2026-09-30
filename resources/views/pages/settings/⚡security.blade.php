<?php

use App\Concerns\PasswordValidationRules;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Title;
use Livewire\Component;
use Flux\Flux;

new #[Title('Change password')] class extends Component {
    use PasswordValidationRules;

    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Update the password for the currently authenticated user.
     */
    public function updatePassword(): void
    {
        try {
            $validated = $this->validate([
                'current_password' => $this->currentPasswordRules(),
                'password' => $this->passwordRules(),
            ]);
        } catch (ValidationException $e) {
            $this->reset('current_password', 'password', 'password_confirmation');

            throw $e;
        }

        Auth::user()->update([
            'password' => $validated['password'],
        ]);

        $this->reset('current_password', 'password', 'password_confirmation');

        Flux::toast(variant: 'success', text: __('Password updated.'));
    }
}; ?>

<section class="w-full">
    <x-pages::settings.layout
        :heading="__('Change Password')"
        :subheading="__('Perbarui kata sandi akun Anda agar tetap aman')">

        <div class="my-6 space-y-6">

            {{-- ═══════════════════════════════════════════ --}}
            {{-- PASSWORD FORM CARD                          --}}
            {{-- ═══════════════════════════════════════════ --}}
            <div class="overflow-hidden rounded-xl border border-stone-200 bg-white dark:border-stone-800 dark:bg-stone-900">

                {{-- Section Header --}}
                <div class="flex items-center gap-3 border-b border-stone-100 px-6 py-4 dark:border-stone-800">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-sage-100 dark:bg-sage-900/60">
                        <svg class="h-4 w-4 text-sage-700 dark:text-sage-400" fill="none"
                            stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-stone-800 dark:text-stone-100">
                            {{ __('Ubah Kata Sandi') }}
                        </h3>
                        <p class="text-[11px] text-stone-500 dark:text-stone-400">
                            {{ __('Pastikan akun Anda menggunakan kata sandi yang panjang dan acak agar tetap aman') }}
                        </p>
                    </div>
                </div>

                {{-- Form Body --}}
                <form wire:submit="updatePassword" class="p-6">
                    <div class="grid grid-cols-1 gap-5">

                        {{-- Current Password --}}
                        <div>
                            <flux:input
                                wire:model="current_password"
                                :label="__('Kata Sandi Saat Ini')"
                                type="password"
                                required
                                autocomplete="current-password"
                                viewable
                            />
                        </div>

                        {{-- New Password --}}
                        <div>
                            <flux:input
                                wire:model="password"
                                :label="__('Kata Sandi Baru')"
                                type="password"
                                required
                                autocomplete="new-password"
                                passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                                viewable
                            />
                        </div>

                        {{-- Confirm Password --}}
                        <div>
                            <flux:input
                                wire:model="password_confirmation"
                                :label="__('Konfirmasi Kata Sandi')"
                                type="password"
                                required
                                autocomplete="new-password"
                                passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                                viewable
                            />
                        </div>
                    </div>

                    {{-- Form Actions --}}
                    <div class="mt-6 flex justify-end border-t border-stone-100 pt-5 dark:border-stone-800">
                        <flux:button variant="primary" type="submit" data-test="update-password-button">
                            {{ __('Simpan Perubahan') }}
                        </flux:button>
                    </div>
                </form>
            </div>

        </div>
    </x-pages::settings.layout>
</section>
