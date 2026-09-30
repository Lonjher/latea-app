<?php

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Flux\Flux;

new #[Title('Pengaturan')] class extends Component {
    use ProfileValidationRules;
    use PasswordValidationRules;
    use WithFileUploads;

    public string $name = '';
    public string $email = '';
    public ?TemporaryUploadedFile $image = null;
    public ?string $existingImageUrl = null;

    // === Change Password state ===
    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function mount(): void
    {
        $user = Auth::user();
        $this->name = $user->name;
        $this->email = $user->email;
        $this->existingImageUrl = $user->avatar ? asset('storage/' . $user->avatar) : null;
    }

    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $rules = $this->profileRules($user->id);
        $rules['image'] = ['nullable', 'image', 'max:2048'];

        $validated = $this->validate($rules);

        // Handle avatar upload — map ke kolom `avatar`
        if ($this->image) {
            // hapus file lama
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }

            // simpan file baru, tapi masukkan ke key `avatar`
            $validated['avatar'] = $this->image->store('avatars', 'public');
        }

        // buang key `image` (TemporaryUploadedFile) supaya tidak ikut di-fill
        unset($validated['image']);

        // reset verifikasi email kalau email berubah
        if (array_key_exists('email', $validated) && $validated['email'] !== $user->email) {
            $user->email_verified_at = null;
        }

        $user->fill($validated);
        $user->save();

        $this->image = null;
        $this->existingImageUrl = $user->fresh()->avatar ? asset('storage/' . $user->fresh()->avatar) : null;

        $this->dispatch('avatar-updated');

        Flux::toast(variant: 'success', text: __('Profile updated.'));
    }

    public function removeAvatar(): void
    {
        $user = Auth::user();

        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        $user->update(['avatar' => null]);

        $this->image = null;
        $this->existingImageUrl = null;

        $this->dispatch('avatar-updated');

        Flux::toast(variant: 'success', text: __('Avatar removed.'));
    }

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

    #[Computed]
    public function hasUnverifiedEmail(): bool
    {
        return Auth::user() instanceof MustVerifyEmail && !Auth::user()->hasVerifiedEmail();
    }

    #[Computed]
    public function userInitials(): string
    {
        $name = Auth::user()->name;
        $parts = preg_split('/\s+/', trim($name));

        if (count($parts) >= 2) {
            return strtoupper(mb_substr($parts[0], 0, 1) . mb_substr($parts[1], 0, 1));
        }

        return strtoupper(mb_substr($name, 0, 2));
    }

    #[Computed]
    public function memberSince(): string
    {
        return Auth::user()->created_at?->translatedFormat('F Y') ?? '—';
    }
}; ?>

<section class="w-full">
    <x-pages::settings.layout :heading="__('Profile')" :subheading="__('Kelola informasi akun dan avatar Anda')">

        <div class="my-6 space-y-6">

            {{-- ═══════════════════════════════════════════ --}}
            {{-- PROFILE CARD — Avatar + Info                --}}
            {{-- ═══════════════════════════════════════════ --}}
            <div
                class="overflow-hidden rounded-xl border border-stone-200 bg-white dark:border-stone-800 dark:bg-stone-900">

                {{-- Cover / Banner --}}
                <div
                    class="relative h-24 bg-gradient-to-br from-sage-400 via-sage-500 to-sage-600 dark:from-sage-800 dark:via-sage-700 dark:to-sage-900">
                    <div class="absolute inset-0 opacity-20"
                        style="background-image: radial-gradient(circle at 20% 30%, rgba(255,255,255,0.4) 0, transparent 40%), radial-gradient(circle at 80% 70%, rgba(255,255,255,0.3) 0, transparent 35%);">
                    </div>
                </div>

                {{-- Avatar + Info --}}
                <div class="relative px-6 pb-6">
                    <div class="flex flex-col items-center gap-4 sm:flex-row sm:items-end sm:gap-5">

                        {{-- Avatar --}}
                        <div class="-mt-12 shrink-0 sm:-mt-14">
                            <div x-data="{
                                previewUrl: null,
                                fileName: '',
                                existingUrl: @js($existingImageUrl),
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
                                },
                                get currentImage() { return this.previewUrl || this.existingUrl; }
                            }" x-init="$watch('$wire.existingImageUrl', value => {
                                existingUrl = value;
                                previewUrl = null;
                                fileName = '';
                            });"
                                @avatar-updated.window="
                                existingUrl = @js($existingImageUrl);
                                previewUrl = null;
                                fileName = '';
                            ">

                                <div class="group relative">
                                    {{-- Preview gambar --}}
                                    <template x-if="currentImage">
                                        <img :src="currentImage" alt="{{ $name }}"
                                            x-on:error="existingUrl = null"
                                            class="h-24 w-24 rounded-full border-4 border-white object-cover shadow-lg ring-1 ring-stone-200 sm:h-28 sm:w-28 dark:border-stone-900 dark:ring-stone-700" />
                                    </template>

                                    {{-- Fallback initials --}}
                                    <template x-if="!currentImage">
                                        <div
                                            class="flex h-24 w-24 items-center justify-center rounded-full border-4 border-white bg-gradient-to-br from-sage-500 to-sage-700 text-2xl font-bold text-white shadow-lg ring-1 ring-stone-200 sm:h-28 sm:w-28 sm:text-3xl dark:border-stone-900 dark:ring-stone-700">
                                            {{ $this->userInitials }}
                                        </div>
                                    </template>

                                    {{-- Hover overlay --}}
                                    <label for="avatar-upload"
                                        class="absolute inset-0 flex cursor-pointer items-center justify-center rounded-full bg-black/50 opacity-0 transition-opacity group-hover:opacity-100">
                                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor"
                                            stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" />
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z" />
                                        </svg>
                                    </label>

                                    <input x-ref="fileInput" wire:model="image" type="file" accept="image/*"
                                        class="hidden" id="avatar-upload" x-on:change="handleFile($event)" />
                                </div>

                                {{-- Loading --}}
                                <div wire:loading wire:target="image"
                                    class="mt-2 flex items-center justify-center gap-1.5 text-[10px] text-sage-600 dark:text-sage-400">
                                    <svg class="h-3 w-3 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                            stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                                    </svg>
                                    {{ __('Mengupload...') }}
                                </div>

                                @error('image')
                                    <p class="mt-1 text-center text-[10px] text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        {{-- Info --}}
                        <div class="flex-1 text-center sm:pb-1 sm:text-left">
                            <h3 class="text-lg font-semibold text-stone-900 dark:text-stone-50">
                                {{ $name }}
                            </h3>
                            <p class="mt-0.5 text-xs text-stone-500 dark:text-stone-400">
                                {{ $email }}
                            </p>
                            <p class="mt-1 text-[10px] text-stone-400 dark:text-stone-500">
                                {{ __('Anggota sejak :month', ['month' => $this->memberSince]) }}
                            </p>

                            {{-- ─── Toko yang di-assign ─── --}}
                            @if (Auth::user()->store)
                                <div class="mt-2 flex flex-wrap items-center justify-center gap-1.5 sm:justify-start">
                                    <span class="text-[10px] font-medium text-stone-400 dark:text-stone-500">
                                        {{ __('Toko:') }}
                                    </span>
                                    <span
                                        class="inline-flex items-center gap-1 rounded-full bg-sage-50 px-2 py-0.5 text-[10px] font-medium text-sage-700 ring-1 ring-sage-200 dark:bg-sage-900/40 dark:text-sage-300 dark:ring-sage-800">
                                        <svg class="h-2.5 w-2.5" fill="none" stroke="currentColor" stroke-width="2"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.65V9.35m0 0a3.001 3.001 0 003.75-.615A2.993 2.993 0 009.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 002.25 1.016c.896 0 1.7-.393 2.25-1.016a3.001 3.001 0 003.75.614m-16.5 0a3.004 3.004 0 01-.621-4.72L4.318 3.44A1.5 1.5 0 015.378 3h13.243a1.5 1.5 0 011.06.44l1.19 1.189a3 3 0 01-.621 4.72m-13.5 8.65h3.75a.75.75 0 00.75-.75V13.5a.75.75 0 00-.75-.75H6.75a.75.75 0 00-.75.75v3.75c0 .414.336.75.75.75z" />
                                        </svg>
                                        {{ Auth::user()->store->name }}
                                    </span>
                                </div>
                            @else
                                <p class="mt-2 text-[10px] italic text-stone-400 dark:text-stone-500">
                                    {{ __('Belum di-assign ke toko manapun') }}
                                </p>
                            @endif
                        </div>

                        {{-- Action Buttons --}}
                        <div class="flex items-center gap-2 sm:pb-1">
                            <label for="avatar-upload"
                                class="inline-flex cursor-pointer items-center gap-1.5 rounded-lg border border-stone-200 bg-white px-3 py-1.5 text-[11px] font-medium text-stone-700 transition hover:bg-stone-50 dark:border-stone-700 dark:bg-stone-800 dark:text-stone-300 dark:hover:bg-stone-700">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" />
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z" />
                                </svg>
                                {{ $existingImageUrl ? __('Ganti') : __('Upload') }}
                            </label>

                            @if ($existingImageUrl)
                                <button type="button" wire:click="removeAvatar"
                                    wire:confirm="{{ __('Hapus avatar?') }}"
                                    class="inline-flex cursor-pointer items-center gap-1.5 rounded-lg border border-red-200 bg-white px-3 py-1.5 text-[11px] font-medium text-red-600 transition hover:bg-red-50 dark:border-red-900/50 dark:bg-stone-800 dark:text-red-400 dark:hover:bg-red-950/30">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                    </svg>
                                    {{ __('Hapus') }}
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- ═══════════════════════════════════════════ --}}
            {{-- PROFILE INFORMATION FORM                    --}}
            {{-- ═══════════════════════════════════════════ --}}
            <div
                class="overflow-hidden rounded-xl border border-stone-200 bg-white dark:border-stone-800 dark:bg-stone-900">

                <div class="flex items-center gap-3 border-b border-stone-100 px-6 py-4 dark:border-stone-800">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-sage-100 dark:bg-sage-900/60">
                        <svg class="h-4 w-4 text-sage-700 dark:text-sage-400" fill="none" stroke="currentColor"
                            stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-stone-800 dark:text-stone-100">
                            {{ __('Informasi Profil') }}
                        </h3>
                        <p class="text-[11px] text-stone-500 dark:text-stone-400">
                            {{ __('Perbarui nama dan alamat email Anda') }}
                        </p>
                    </div>
                </div>

                <form wire:submit="updateProfileInformation" class="p-6">
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div class="sm:col-span-1">
                            <flux:input wire:model="name" :label="__('Nama')" type="text" required autofocus
                                autocomplete="name" />
                        </div>

                        <div class="sm:col-span-1">
                            <flux:input wire:model="email" :label="__('Email')" type="email" required
                                autocomplete="email" />
                        </div>

                        @if ($this->hasUnverifiedEmail)
                            <div class="sm:col-span-2">
                                <div
                                    class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 dark:border-amber-900/50 dark:bg-amber-950/30">
                                    <div class="flex items-start gap-2.5">
                                        <svg class="mt-0.5 h-4 w-4 shrink-0 text-amber-600 dark:text-amber-400"
                                            fill="none" stroke="currentColor" stroke-width="2"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                                        </svg>
                                        <div class="flex-1">
                                            <p class="text-xs font-medium text-amber-800 dark:text-amber-300">
                                                {{ __('Email belum diverifikasi') }}
                                            </p>
                                            <p class="mt-0.5 text-[11px] text-amber-700 dark:text-amber-400">
                                                {{ __('Silakan verifikasi email Anda untuk mengakses semua fitur.') }}
                                            </p>
                                            <button type="button" wire:click.prevent="resendVerificationNotification"
                                                class="mt-2 text-[11px] font-medium text-amber-800 underline transition hover:text-amber-900 dark:text-amber-300 dark:hover:text-amber-200">
                                                {{ __('Kirim ulang email verifikasi') }}
                                            </button>

                                            @if (session('status') === 'verification-link-sent')
                                                <p
                                                    class="mt-2 text-[11px] font-medium text-green-600 dark:text-green-400">
                                                    {{ __('Link verifikasi baru telah dikirim ke email Anda.') }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="mt-6 flex justify-end border-t border-stone-100 pt-5 dark:border-stone-800">
                        <flux:button variant="primary" type="submit" data-test="update-profile-button">
                            {{ __('Simpan Perubahan') }}
                        </flux:button>
                    </div>
                </form>
            </div>

            {{-- ═══════════════════════════════════════════ --}}
            {{-- PASSWORD FORM CARD  (full width)            --}}
            {{-- ═══════════════════════════════════════════ --}}
            <div
                class="overflow-hidden rounded-xl border border-stone-200 bg-white dark:border-stone-800 dark:bg-stone-900">

                <div class="flex items-center gap-3 border-b border-stone-100 px-6 py-4 dark:border-stone-800">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-sage-100 dark:bg-sage-900/60">
                        <svg class="h-4 w-4 text-sage-700 dark:text-sage-400" fill="none" stroke="currentColor"
                            stroke-width="2" viewBox="0 0 24 24">
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

                <form wire:submit="updatePassword" class="p-6">
                    {{-- ⬇️ Kunci: batasi lebar konten form --}}
                    <div class="mx-auto w-full max-w-md space-y-5">

                        <flux:input wire:model="current_password" :label="__('Kata Sandi Saat Ini')" type="password"
                            required autocomplete="current-password" viewable />

                        <flux:input wire:model="password" :label="__('Kata Sandi Baru')" type="password" required
                            autocomplete="new-password"
                            passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                            viewable />

                        <flux:input wire:model="password_confirmation" :label="__('Konfirmasi Kata Sandi')"
                            type="password" required autocomplete="new-password"
                            passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                            viewable />

                    </div>

                    {{-- Actions — tetap full-width, tombol di kanan --}}
                    <div
                        class="mx-auto mt-6 flex w-full max-w-md justify-end border-t border-stone-100 pt-5 dark:border-stone-800">
                        <flux:button variant="primary" type="submit" data-test="update-password-button">
                            {{ __('Simpan Perubahan') }}
                        </flux:button>
                    </div>
                </form>
            </div>
        </div>
    </x-pages::settings.layout>
</section>
