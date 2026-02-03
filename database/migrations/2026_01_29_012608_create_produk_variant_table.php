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
        Schema::create('produk_variant', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produk_id')->constrained('produk')->onDelete('cascade');
            $table->string('nama_variant', 255)->notNullable();
            $table->decimal('harga_tambahan', 10, 2)->default(0);
            $table->text('gambar_variant')->nullable();
            $table->timestamps();

            // Index
            $table->index('produk_id', 'idx_variant_produk_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produk_variant');
    }
};