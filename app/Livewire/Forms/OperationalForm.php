<?php

namespace App\Livewire\Forms;

use Livewire\Form;
use App\Models\OperationalCost;
use Illuminate\Validation\Rule;

class OperationalForm extends Form
{
    public ?OperationalCost $operationalModel = null;

    public $operational_name;
    public $cost;
    public $store_id;

    public function rules(): array
    {
        return [
            'operational_name' => ['required', 'string', 'max:255'],
            'cost'             => ['required', 'numeric', 'min:0'],
            'store_id'         => ['required', 'integer', Rule::exists('stores', 'id')],
        ];
    }

    public function messages(): array
    {
        return [
            'operational_name.required' => 'Nama kegiatan wajib diisi.',
            'operational_name.max'      => 'Nama kegiatan maksimal 255 karakter.',
            'cost.required'             => 'Biaya wajib diisi.',
            'cost.numeric'              => 'Biaya harus berupa angka.',
            'cost.min'                  => 'Biaya tidak boleh negatif.',
            'store_id.required'         => 'Toko wajib dipilih.',
            'store_id.exists'           => 'Toko tidak valid.',
        ];
    }

    public function setOperational(OperationalCost $op): void
    {
        $this->operationalModel = $op;
        $this->operational_name = $op->operational;
        $this->cost             = $op->cost;
        $this->store_id         = $op->store_id;   // ⭐ penting
    }

    public function create()
    {
        $this->validate();

        OperationalCost::create([
            'store_id'    => $this->store_id,
            'operational' => $this->operational_name,
            'cost'        => $this->cost,
        ]);

        return $this->reset();
    }

    public function update()
    {
        $this->validate();

        $this->operationalModel->update([
            'store_id'    => $this->store_id,      // ⭐ boleh diubah (admin)
            'operational' => $this->operational_name,
            'cost'        => $this->cost,
        ]);

        return $this->reset();
    }
}
