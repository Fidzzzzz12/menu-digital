<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update data NULL menjadi empty string terlebih dahulu
        DB::table('pesanan')->whereNull('kurir')->update(['kurir' => '']);
        DB::table('pesanan')->whereNull('layanan_kurir')->update(['layanan_kurir' => '']);
        DB::table('pesanan')->whereNull('estimasi_kirim')->update(['estimasi_kirim' => '']);
        
        // Sekarang ubah struktur kolom
        Schema::table('pesanan', function (Blueprint $table) {
            $table->string('kurir', 100)->nullable()->change();
            $table->string('layanan_kurir', 100)->nullable()->change();
            $table->string('estimasi_kirim', 50)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pesanan', function (Blueprint $table) {
            $table->string('kurir', 50)->nullable()->change();
            $table->string('layanan_kurir', 50)->nullable()->change();
            $table->string('estimasi_kirim', 20)->nullable()->change();
        });
    }
};
