<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StoreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $stores = [
            [
                'name' => 'Store 1',
                'code' => 'STORE001',
                'location' => 'Location 1',
                'is_active' => true,
            ],
            [
                'name' => 'Store 2',
                'code' => 'STORE002',
                'location' => 'Location 2',
                'is_active' => true,
            ],
            [
                'name' => 'Store 3',
                'code' => 'STORE003',
                'location' => 'Location 3',
                'is_active' => true,
            ],
        ];

        foreach ($stores as $store) {
            \App\Models\Store::create($store);
        }
    }
}
