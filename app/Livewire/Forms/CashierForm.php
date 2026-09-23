<?php

namespace App\Livewire\Forms;

use Illuminate\Validation\Rule;
use Livewire\Form;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CashierForm extends Form
{
    public ?User $cashier = null;

    public $name;
    public $email;
    public $password;
    public $password_confirmation;
    public $store_id;
    public $is_active = true;

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
    }

    public function create()
    {
        $this->validate();

        User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
            'role_id' => 2,
            'store_id' => $this->store_id,
            'is_active' => $this->is_active,
            'email_verified_at' => now(),
        ]);

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

        $this->cashier->update($data);

        return $this->reset();
    }
}
