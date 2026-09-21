<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'code' => 'PROD-001',
                'name' => 'Teh Original',
                'description' => 'Deskripsi untuk Teh Original',
                'image' => 'products/v6E43P4WAFnDn5bkdR5jTurKVu5DTv9QUXztKDLV.jpg',
                'initial_price' => 3000.00,
                'discount_price' => 5500.00,
                'minimal_discount' => 10,
                'price' => 6000.00,
                'is_active' => true,
            ],
            [
                'code' => 'PROD-002',
                'name' => 'Teh Rasa Susu',
                'description' => 'Deskripsi untuk Produk Teh Rasa Susu',
                'image' => 'products/4CMoERMT17K7xxA9vFlEvLqWhAfYnG8AM7vNrv9L.jpg',
                'initial_price' => 4000.00,
                'discount_price' => 5500.00,
                'minimal_discount' => 20,
                'price' => 7000.00,
                'is_active' => true,
            ],
            [
                'code'=> 'PROD-003',
                'name' => 'Teh Rasa Taro',
                'description' => 'Deskripsi untuk Produk Teh Rasa Taro',
                'image' => 'products/EtWEZBPOiJ5xdfDWbkwLJ8VHrikVmDrhPawhUlsh.jpg',
                'initial_price' => 4000.00,
                'discount_price' => 7500.00,
                'minimal_discount' => 30,
                'price' => 8000.00,
                'is_active' => true,
            ],
            [
                'code'=> 'PROD-004',
                'name' => 'Teh Rasa Matcha',
                'description' => 'Deskripsi untuk Produk Teh Rasa Matcha',
                'image' => 'products/fMt3qBhKYC8ZU1pzrvlbDxUe780nLJnd8H1kHzqV.jpg',
                'initial_price' => 4000.00,
                'discount_price' => 9500.00,
                'minimal_discount' => 30,
                'price' => 10000.00,
                'is_active' => true,
            ],
        ];

        foreach ($products as $product) {
            \App\Models\Product::create($product);
        }
    }
}
