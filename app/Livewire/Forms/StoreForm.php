<?php

namespace App\Livewire\Forms;

use Illuminate\Validation\Rule;
use Livewire\Form;
use App\Models\Store;

class StoreForm extends Form
{
    public ?Store $store = null;

    public $name;
    public $code;
    public $location;
    public $is_active;

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:100', Rule::unique('stores', 'code')->ignore($this->store?->id, 'id')],
            'location' => ['required', 'string', 'max:255'],
            'is_active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            // Nama Lengkap
            'name.required' => 'Nama wajib diisi.',
            'name.string' => 'Nama harus berupa teks.',
            'name.max' => 'Nama maksimal 255 karakter.',

            // NIK
            'code.required' => 'Code wajib diisi.',
            'code.string' => 'Code harus berupa teks.',
            'code.size' => 'Code harus tepat 16 digit angka.',
            'code.unique' => 'Code sudah terdaftar, silakan gunakan Code lain.',

            // Location
            'location.required' => 'Location wajib diisi.',
            'location.string' => 'Location harus berupa teks.',
            'location.max' => 'Location maksimal 255 karakter.',

            // Active
            'is_active.boolean' => 'Status aktif harus berupa nilai boolean.',
        ];
    }

    public function setStore(Store $store): void
    {
        $this->store = $store;
        $this->name = $store->name;
        $this->code = $store->code;
        $this->location = $store->location;
        $this->is_active = $store->is_active;
    }

    public function create()
    {
        $this->validate();

        Store::create([
            'name' => $this->name,
            'code' => $this->code,
            'location' => $this->location,
            'is_active' => $this->is_active,
        ]);

        return $this->reset();
    }

    public function update()
    {
        $this->validate();
        $this->store->update($this->all());
        return $this->reset();
    }
}
