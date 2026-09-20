<?php

namespace App\Livewire\Forms;

use Illuminate\Validation\Rule;
use Livewire\Form;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;

class ProductForm extends Form
{
    public ?Product $product = null;

    public $code;
    public $name;
    public $description;
    public ?TemporaryUploadedFile $image = null;
    public $initial_price;      // HPP
    public $discount_price;     // Harga promo
    public $minimal_discount;   // Qty minimum untuk promo
    public $price;              // Harga jual normal
    public $is_active;

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:255', Rule::unique('products', 'code')->ignore($this->product?->id, 'id')],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'image' => ['nullable', 'image', 'max:2048'],
            'initial_price' => ['required', 'numeric', 'min:0'],           // HPP
            'discount_price' => ['nullable', 'numeric', 'min:0', 'lt:price'], // Diskon < harga jual
            'minimal_discount' => ['nullable', 'integer', 'min:1', 'required_with:discount_price'],
            'price' => ['required', 'numeric', 'min:0'],
            'is_active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            // Code
            'code.required' => 'Code wajib diisi.',
            'code.string' => 'Code harus berupa teks.',
            'code.max' => 'Code maksimal 255 karakter.',
            'code.unique' => 'Code sudah terdaftar, silakan gunakan Code lain.',

            // Name
            'name.required' => 'Nama wajib diisi.',
            'name.string' => 'Nama harus berupa teks.',
            'name.max' => 'Nama maksimal 255 karakter.',

            // Description
            'description.required' => 'Deskripsi wajib diisi.',
            'description.string' => 'Deskripsi harus berupa teks.',

            // Image
            'image.image' => 'File harus berupa gambar.',
            'image.max' => 'Ukuran gambar maksimal 2MB.',

            // HPP (initial_price)
            'initial_price.required' => 'HPP wajib diisi.',
            'initial_price.numeric' => 'HPP harus berupa angka.',
            'initial_price.min' => 'HPP tidak boleh negatif.',

            // Discount Price
            'discount_price.numeric' => 'Harga diskon harus berupa angka.',
            'discount_price.min' => 'Harga diskon tidak boleh negatif.',
            'discount_price.lt' => 'Harga diskon harus lebih murah dari harga jual normal.',

            // Minimal Discount
            'minimal_discount.integer' => 'Minimal qty diskon harus berupa angka bulat.',
            'minimal_discount.min' => 'Minimal qty diskon minimal 1.',
            'minimal_discount.required_with' => 'Minimal qty wajib diisi jika harga diskon diisi.',

            // Price
            'price.required' => 'Harga jual wajib diisi.',
            'price.numeric' => 'Harga jual harus berupa angka.',
            'price.min' => 'Harga jual tidak boleh negatif.',

            // Active
            'is_active.boolean' => 'Status aktif harus berupa nilai boolean.',
        ];
    }

    public function setProduct(Product $product): void
    {
        $this->product = $product;
        $this->code = $product->code;
        $this->name = $product->name;
        $this->description = $product->description;
        $this->image = null;
        $this->initial_price = $product->getRawOriginal('initial_price');
        $this->discount_price = $product->getRawOriginal('discount_price');
        $this->price = $product->getRawOriginal('price');
        $this->minimal_discount = $product->minimal_discount;
        $this->is_active = (bool) $product->is_active;
    }

    public function create()
    {
        $this->validate();

        $imagePath = $this->image
            ? $this->image->store('products', 'public')
            : null;

        Product::create([
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'image' => $imagePath,
            'initial_price' => $this->initial_price,
            'discount_price' => $this->discount_price,
            'minimal_discount' => $this->minimal_discount,
            'price' => $this->price,
            'is_active' => $this->is_active,
        ]);

        return $this->reset();
    }

    public function update()
    {
        $this->validate();

        if ($this->image) {
            if ($this->product->image && Storage::disk('public')->exists($this->product->image)) {
                Storage::disk('public')->delete($this->product->image);
            }
            $imagePath = $this->image->store('products', 'public');
        } else {
            $imagePath = $this->product->image;
        }

        $this->product->update([
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'image' => $imagePath,
            'initial_price' => $this->initial_price,
            'discount_price' => $this->discount_price,
            'minimal_discount' => $this->minimal_discount,
            'price' => $this->price,
            'is_active' => $this->is_active,
        ]);

        return $this->reset();
    }
}
