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
        Schema::create('dashboard_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->decimal('total_pendapatan', 12, 2)->default(0);
            $table->integer('jumlah_transaksi')->default(0);
            $table->integer('jumlah_menu')->default(0);
            $table->integer('jumlah_kategori')->default(0);
            $table->integer('bulan')->comment('1-12');
            $table->integer('tahun')->comment('YYYY');
            $table->timestamps();

            // Indexes
            $table->index('user_id', 'idx_stats_user_id');
            $table->index(['bulan', 'tahun'], 'idx_stats_bulan_tahun');
            $table->unique(['user_id', 'bulan', 'tahun'], 'idx_stats_user_bulan_tahun');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dashboard_stats');
    }
};