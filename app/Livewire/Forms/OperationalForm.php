<?php

namespace App\Livewire\Forms;

use App\Models\OperationalCost;
use App\Models\Store;
use Livewire\Attributes\Validate;
use Livewire\Form;

class OperationalForm extends Form
{
    public ?OperationalCost $operationalCost = null;

    public $store_id;
    public $operational;
    public $cost;

    public function rules(): array
    {
        return [
            'store_id' => ['required', 'exists:stores,id'],
            'operational' => ['required', 'string', 'max:255'],
            'cost' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            // Code
            'store_id.required' => 'Store wajib diisi.',
            'store_id.string' => 'Store harus harus ada.',

            // Name
            'operational.required' => 'Kegiatan wajib diisi.',
            'operational.string' => 'Kegiatan harus berupa teks.',
            'operational.max' => 'Kegiatan maksimal 255 karakter.',

            // Description
            'cost.required' => 'Biaya wajib diisi.',
            'cost.decimal' => 'Biaya harus berupa decimal.',
        ];
    }

    public function setOperational(OperationalCost $operationalCost): void
    {
        $this->operationalCost = $operationalCost;
        $this->store_id = $operationalCost->store_id;
        $this->operational = $operationalCost->operational;
        $this->cost = $operationalCost->cost;
    }

    public function create()
    {
        $this->validate();

        OperationalCost::create([
            'store_id' => $this->store_id,
            'operational' => $this->operational,
            'cost' => $this->cost,
        ]);

        return $this->reset();
    }

    public function update()
    {
        $this->validate();

        $this->operationalCost->update([
            'store_id' => $this->store_id,
            'operational' => $this->operational,
            'cost' => $this->cost,
        ]);

        return $this->reset();
    }
}
