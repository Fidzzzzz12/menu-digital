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
        Schema::create('statistik_harian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->date('tanggal')->notNullable();
            $table->decimal('total_pendapatan', 10, 2)->default(0);
            $table->integer('jumlah_transaksi')->default(0);
            $table->timestamps();

            // Indexes
            $table->index('user_id', 'idx_statistik_user_id');
            $table->index('tanggal', 'idx_statistik_tanggal');
            $table->unique(['user_id', 'tanggal'], 'idx_statistik_user_tanggal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('statistik_harian');
    }
};