<?php

namespace App\Livewire\Forms;

use Illuminate\Validation\Rule;
use Livewire\Form;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use App\Models\Store;
use Illuminate\Support\Facades\Storage;

class StoreForm extends Form
{
    public ?Store $store = null;

    public $name;
    public $code;
    public $location;
    public ?TemporaryUploadedFile $image = null;
    public $is_active;

    public function rules(): array
    {
        return [
            'name'     => ['required', 'string', 'max:255'],
            'code'     => ['required', 'string', 'max:100', Rule::unique('stores', 'code')->ignore($this->store?->id, 'id')],
            'location' => ['required', 'string', 'max:255'],
            'image'    => ['nullable', 'image', 'max:2048'], // max 2MB
            'is_active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            // Name
            'name.required' => 'Nama wajib diisi.',
            'name.string'   => 'Nama harus berupa teks.',
            'name.max'      => 'Nama maksimal 255 karakter.',

            // Code
            'code.required' => 'Code wajib diisi.',
            'code.string'   => 'Code harus berupa teks.',
            'code.max'      => 'Code maksimal 100 karakter.',
            'code.unique'   => 'Code sudah terdaftar, silakan gunakan Code lain.',

            // Location
            'location.required' => 'Location wajib diisi.',
            'location.string'   => 'Location harus berupa teks.',
            'location.max'      => 'Location maksimal 255 karakter.',

            // Image
            'image.image' => 'File harus berupa gambar.',
            'image.max'   => 'Ukuran gambar maksimal 2MB.',

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
        $this->image = null; // user upload baru kalau mau ganti
        $this->is_active = $store->is_active;
    }

    public function create()
    {
        $this->validate();

        $imagePath = $this->image
            ? $this->image->store('stores', 'public')
            : null;

        Store::create([
            'name'       => $this->name,
            'code'       => $this->code,
            'location'   => $this->location,
            'image'      => $imagePath,
            'is_active'  => $this->is_active,
        ]);

        return $this->reset();
    }

    public function update()
    {
        $this->validate();

        // Simpan gambar baru kalau ada
        if ($this->image) {
            // Hapus gambar lama
            if ($this->store->image && Storage::disk('public')->exists($this->store->image)) {
                Storage::disk('public')->delete($this->store->image);
            }
            $imagePath = $this->image->store('stores', 'public');
        } else {
            // Pertahankan gambar lama
            $imagePath = $this->store->image;
        }

        $this->store->update([
            'name'      => $this->name,
            'code'      => $this->code,
            'location'  => $this->location,
            'image'     => $imagePath,
            'is_active' => $this->is_active,
        ]);

        return $this->reset('image');
    }
}
