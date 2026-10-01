<?php

namespace App\Livewire\Forms;

use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Form;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use App\Models\User;

class CashierForm extends Form
{
    public ?User $cashier = null;

    public $name;
    public $email;
    public $password;
    public $password_confirmation;
    public $store_id;
    public $is_active = true;

    // === Avatar ===
    public ?TemporaryUploadedFile $avatar = null;
    public ?string $existingAvatarUrl = null;

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($this->cashier?->id, 'id'),
            ],
            'password' => $this->cashier
                ? ['nullable', 'string', 'min:8', 'confirmed']
                : ['required', 'string', 'min:8', 'confirmed'],
            'store_id' => ['required', 'exists:stores,id'],
            'is_active' => ['boolean'],

            // === Avatar rules ===
            'avatar' => ['nullable', 'image', 'max:2048'], // maks 2MB
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama wajib diisi.',
            'name.max' => 'Nama maksimal 255 karakter.',

            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',

            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',

            'store_id.required' => 'Store wajib dipilih.',
            'store_id.exists' => 'Store tidak valid.',

            'is_active.boolean' => 'Status aktif harus berupa boolean.',

            'avatar.image' => 'File harus berupa gambar.',
            'avatar.max' => 'Ukuran gambar maksimal 2MB.',
        ];
    }

    public function setCashier(User $cashier): void
    {
        $this->cashier = $cashier;
        $this->name = $cashier->name;
        $this->email = $cashier->email;
        $this->password = null;
        $this->password_confirmation = null;
        $this->store_id = $cashier->store_id;
        $this->is_active = (bool) $cashier->is_active;

        $this->avatar = null;
        $this->existingAvatarUrl = $cashier->avatar
            ? asset('storage/' . $cashier->avatar)
            : null;
    }

    public function create()
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
            'role_id' => 2,
            'store_id' => $this->store_id,
            'is_active' => $this->is_active,
            'email_verified_at' => now(),
        ];

        // Simpan avatar kalau ada
        if ($this->avatar) {
            $data['avatar'] = $this->avatar->store('avatars', 'public');
        }

        User::create($data);

        return $this->reset();
    }

    public function update()
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'store_id' => $this->store_id,
            'is_active' => $this->is_active,
        ];

        // Hanya update password kalau diisi
        if ($this->password) {
            $data['password'] = Hash::make($this->password);
        }

        // Handle avatar baru
        if ($this->avatar) {
            // Hapus file lama
            if ($this->cashier->avatar && Storage::disk('public')->exists($this->cashier->avatar)) {
                Storage::disk('public')->delete($this->cashier->avatar);
            }

            $data['avatar'] = $this->avatar->store('avatars', 'public');
        }

        $this->cashier->update($data);

        return $this->reset();
    }
}
