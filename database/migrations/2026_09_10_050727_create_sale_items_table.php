<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained();
            $table->string('product_name'); // snapshot
            $table->string('product_code')->nullable(); // snapshot
            $table->decimal('price', 15, 2);           // harga normal saat transaksi
            $table->decimal('discount_price', 15, 2)->nullable(); // harga diskon saat transaksi
            $table->decimal('effective_price', 15, 2); // harga aktual per item
            $table->unsignedInteger('quantity');
            $table->decimal('line_total', 15, 2);         // effective_price * quantity
            $table->timestamps();

            $table->index('sale_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_items');
    }
};
