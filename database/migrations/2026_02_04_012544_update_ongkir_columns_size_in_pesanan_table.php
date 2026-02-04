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
        Schema::table('pesanan', function (Blueprint $table) {
            // Perbesar kolom kurir dari VARCHAR(50) ke VARCHAR(100)
            // Untuk simpan nama lengkap seperti "Jalur Nugraha Ekakurir (JNE)"
            $table->string('kurir', 100)->nullable()->change();
            
            // Perbesar kolom layanan_kurir dari VARCHAR(50) ke VARCHAR(100)
            // Untuk simpan layanan lengkap seperti "JTR>130", "CTCYES", dll
            $table->string('layanan_kurir', 100)->nullable()->change();
            
            // Perbesar kolom estimasi_kirim dari VARCHAR(20) ke VARCHAR(50)
            // Untuk simpan estimasi seperti "2-3 day", "7-14 day"
            $table->string('estimasi_kirim', 50)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pesanan', function (Blueprint $table) {
            // Kembalikan ke ukuran semula (jika rollback)
            $table->string('kurir', 50)->change();
            $table->string('layanan_kurir', 50)->nullable()->change();
            $table->string('estimasi_kirim', 20)->nullable()->change();
        });
    }
};