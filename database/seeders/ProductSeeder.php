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
                'name' => 'Teh Original Mini',
                'description' => 'Deskripsi untuk Teh Original Mini',
                'image' => 'products/v6E43P4WAFnDn5bkdR5jTurKVu5DTv9QUXztKDLV.jpg',
                'initial_price' => 1000.00,
                'discount_price' => 2000.00,
                'minimal_discount' => 0,
                'price' => 2000.00,
                'is_active' => true,
            ],
            [
                'code' => 'PROD-002',
                'name' => 'Teh Original Jumbo',
                'description' => 'Deskripsi untuk Teh Original Jumbo',
                'image' => 'products/v6E43P4WAFnDn5bkdR5jTurKVu5DTv2QUXztKDLV.jpg',
                'initial_price' => 1500.00,
                'discount_price' => 2000.00,
                'minimal_discount' => 0,
                'price' => 3000.00,
                'is_active' => true,
            ],
            [
                'code' => 'PROD-003',
                'name' => 'Milk Tea Mini',
                'description' => 'Deskripsi untuk Produk Milk Tea Mini',
                'image' => 'products/4CMoERMT17K7xxA9vFlEvLqWhAfYnG8AM7vNrv4L.jpg',
                'initial_price' => 3000.00,
                'discount_price' => 5000.00,
                'minimal_discount' => 0,
                'price' => 5000.00,
                'is_active' => true,
            ],
            [
                'code' => 'PROD-004',
                'name' => 'Milk Tea Jumbo',
                'description' => 'Deskripsi untuk Produk Milk Tea Jumbo',
                'image' => 'products/4CMoERMT17K7xxA9vFlEvLqWhAfYnG8AM7vNrv9L.jpg',
                'initial_price' => 3500.00,
                'discount_price' => 7000.00,
                'minimal_discount' => 0,
                'price' => 7000.00,
                'is_active' => true,
            ],
            [
                'code'=> 'PROD-005',
                'name' => 'Teh Rasa Taro Mini',
                'description' => 'Deskripsi untuk Produk Teh Rasa Taro Mini',
                'image' => 'products/EtWEZBPOiJ5xdfDWbkwLJ8VHrikVmDrhPawhUlsh.jpg',
                'initial_price' => 3000.00,
                'discount_price' => 5000.00,
                'minimal_discount' => 0,
                'price' => 5000.00,
                'is_active' => true,
            ],
            [
                'code'=> 'PROD-006',
                'name' => 'Teh Rasa Taro Jumbo',
                'description' => 'Deskripsi untuk Produk Teh Rasa Taro Jumbo',
                'image' => 'products/EtWEZBPOiJ5xdfDWbkwLJ8VHrikVmDrhPawhUlsh.jpg',
                'initial_price' => 3500.00,
                'discount_price' => 7000.00,
                'minimal_discount' => 0,
                'price' => 7000.00,
                'is_active' => true,
            ],
            [
                'code'=> 'PROD-007',
                'name' => 'Teh Rasa Matcha Mini',
                'description' => 'Deskripsi untuk Produk Teh Rasa Matcha Mini',
                'image' => 'products/fMt3qBhKYC8ZU1pzrvlbDxUe780nLJnd8H1kHzqV.jpg',
                'initial_price' => 3000.00,
                'discount_price' => 5000.00,
                'minimal_discount' => 0,
                'price' => 5000.00,
                'is_active' => true,
            ],
            [
                'code'=> 'PROD-008',
                'name' => 'Teh Rasa Matcha Jumbo',
                'description' => 'Deskripsi untuk Produk Teh Rasa Matcha Jumbo',
                'image' => 'products/fMt3qBhKYC8ZU1pzrvlbDxUe780nLJnd8H1kHzqV.jpg',
                'initial_price' => 3500.00,
                'discount_price' => 7000.00,
                'minimal_discount' => 0,
                'price' => 7000.00,
                'is_active' => true,
            ],
            [
                'code'=> 'PROD-009',
                'name' => 'Teh Rasa Thai Mini',
                'description' => 'Deskripsi untuk Produk Teh Rasa Thai Mini',
                'image' => 'products/fMt3qBhKYC8ZU1pzrvlbDxUe780nLJnd8H1kHzqV.jpg',
                'initial_price' => 3000.00,
                'discount_price' => 5000.00,
                'minimal_discount' => 0,
                'price' => 5000.00,
                'is_active' => true,
            ],
            [
                'code'=> 'PROD-010',
                'name' => 'Teh Rasa Thai Jumbo',
                'description' => 'Deskripsi untuk Produk Teh Rasa Thai Jumbo',
                'image' => 'products/fMt3qBhKYC8ZU1pzrvlbDxUe780nLJnd8H1kHzqV.jpg',
                'initial_price' => 3500.00,
                'discount_price' => 7000.00,
                'minimal_discount' => 0,
                'price' => 7000.00,
                'is_active' => true,
            ],
            [
                'code'=> 'PROD-011',
                'name' => 'Teh Rasa Avocado Mini',
                'description' => 'Deskripsi untuk Produk Teh Rasa Avocado Mini',
                'image' => 'products/fMt3qBhKYC8ZU1pzrvlbDxUe780nLJnd8H1kHzqV.jpg',
                'initial_price' => 3000.00,
                'discount_price' => 5000.00,
                'minimal_discount' => 0,
                'price' => 5000.00,
                'is_active' => true,
            ],
            [
                'code'=> 'PROD-012',
                'name' => 'Teh Rasa Avocado Jumbo',
                'description' => 'Deskripsi untuk Produk Teh Rasa Avocado Jumbo',
                'image' => 'products/fMt3qBhKYC8ZU1pzrvlbDxUe780nLJnd8H1kHzqV.jpg',
                'initial_price' => 3500.00,
                'discount_price' => 7000.00,
                'minimal_discount' => 0,
                'price' => 7000.00,
                'is_active' => true,
            ],
            [
                'code'=> 'PROD-013',
                'name' => 'Teh Rasa Kurma Jumbo',
                'description' => 'Deskripsi untuk Produk Teh Rasa Kurma Jumbo',
                'image' => 'products/fMt3qBhKYC8ZU1pzrvlbDxUe780nLJnd8H1kHzqV.jpg',
                'initial_price' => 3500.00,
                'discount_price' => 7000.00,
                'minimal_discount' => 0,
                'price' => 7000.00,
                'is_active' => true,
            ],
        ];

        foreach ($products as $product) {
            \App\Models\Product::create($product);
        }
    }
}
