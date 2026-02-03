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
        Schema::create('pesanan_item', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pesanan_id')->constrained('pesanan')->onDelete('cascade');
            $table->foreignId('produk_id')->nullable()->constrained('produk')->onDelete('set null');
            $table->string('nama_produk', 255)->notNullable();
            $table->string('variant', 255)->nullable();
            $table->decimal('harga', 10, 2)->notNullable();
            $table->integer('quantity')->notNullable();
            $table->decimal('subtotal', 10, 2)->notNullable();
            $table->timestamps();

            // Indexes
            $table->index('pesanan_id', 'idx_pesanan_item_pesanan_id');
            $table->index('produk_id', 'idx_pesanan_item_produk_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pesanan_item');
    }
};