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
            // Add url_toko if not exists
            if (!Schema::hasColumn('pesanan', 'url_toko')) {
                $table->string('url_toko')->after('user_id')->nullable();
            }
            
            // Add metode_pengiriman if not exists
            if (!Schema::hasColumn('pesanan', 'metode_pengiriman')) {
                $table->string('metode_pengiriman')->after('order_date')->nullable();
            }
            
            // Add provinsi_id if not exists
            if (!Schema::hasColumn('pesanan', 'provinsi_id')) {
                $table->integer('provinsi_id')->nullable()->after('metode_pengiriman');
            }
            
            // Add provinsi_nama if not exists
            if (!Schema::hasColumn('pesanan', 'provinsi_nama')) {
                $table->string('provinsi_nama')->nullable()->after('provinsi_id');
            }
            
            // Add kota_id if not exists
            if (!Schema::hasColumn('pesanan', 'kota_id')) {
                $table->integer('kota_id')->nullable()->after('provinsi_nama');
            }
            
            // Add kota_nama if not exists
            if (!Schema::hasColumn('pesanan', 'kota_nama')) {
                $table->string('kota_nama')->nullable()->after('kota_id');
            }
            
            // Add kurir if not exists
            if (!Schema::hasColumn('pesanan', 'kurir')) {
                $table->string('kurir')->nullable()->after('kota_nama');
            }
            
            // Add layanan_kurir if not exists
            if (!Schema::hasColumn('pesanan', 'layanan_kurir')) {
                $table->string('layanan_kurir')->nullable()->after('kurir');
            }
            
            // Add layanan_pengiriman if not exists
            if (!Schema::hasColumn('pesanan', 'layanan_pengiriman')) {
                $table->string('layanan_pengiriman')->nullable()->after('layanan_kurir');
            }
            
            // Add estimasi_kirim if not exists
            if (!Schema::hasColumn('pesanan', 'estimasi_kirim')) {
                $table->string('estimasi_kirim')->nullable()->after('layanan_pengiriman');
            }
            
            // Add ongkir if not exists
            if (!Schema::hasColumn('pesanan', 'ongkir')) {
                $table->integer('ongkir')->default(0)->after('estimasi_kirim');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pesanan', function (Blueprint $table) {
            $columns = [
                'url_toko',
                'metode_pengiriman',
                'provinsi_id',
                'provinsi_nama',
                'kota_id',
                'kota_nama',
                'kurir',
                'layanan_kurir',
                'layanan_pengiriman',
                'estimasi_kirim',
                'ongkir'
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('pesanan', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
