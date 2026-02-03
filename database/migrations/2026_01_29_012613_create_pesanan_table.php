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
        // Create pesanan table if it doesn't exist
        if (!Schema::hasTable('pesanan')) {
            Schema::create('pesanan', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->string('url_toko');
                $table->string('order_id')->unique();
                $table->string('nama_lengkap');
                $table->string('whatsapp');
                $table->text('alamat');
                $table->text('catatan')->nullable();
                $table->decimal('total_harga', 10, 2);
                $table->enum('status', ['pending', 'dikonfirmasi', 'selesai', 'dibatalkan'])->default('pending');
                $table->timestamp('order_date')->useCurrent();
                
                // Shipping fields
                $table->string('metode_pengiriman')->nullable();
                $table->integer('provinsi_id')->nullable();
                $table->string('provinsi_nama')->nullable();
                $table->integer('kota_id')->nullable();
                $table->string('kota_nama')->nullable();
                $table->string('kurir')->nullable();
                $table->string('layanan_kurir')->nullable();
                $table->string('layanan_pengiriman')->nullable();
                $table->string('estimasi_kirim')->nullable();
                $table->integer('ongkir')->default(0);
                
                $table->timestamps();
                
                // Indexes
                $table->index('user_id');
                $table->index('order_id');
                $table->index('status');
                $table->index('order_date');
            });
        } else {
            // Add missing columns if table exists
            Schema::table('pesanan', function (Blueprint $table) {
                if (!Schema::hasColumn('pesanan', 'url_toko')) {
                    $table->string('url_toko')->after('user_id');
                }
                if (!Schema::hasColumn('pesanan', 'ongkir')) {
                    $table->integer('ongkir')->default(0)->after('catatan');
                }
                if (!Schema::hasColumn('pesanan', 'kurir')) {
                    $table->string('kurir')->nullable()->after('ongkir');
                }
                if (!Schema::hasColumn('pesanan', 'layanan_kurir')) {
                    $table->string('layanan_kurir')->nullable()->after('kurir');
                }
                if (!Schema::hasColumn('pesanan', 'estimasi_kirim')) {
                    $table->string('estimasi_kirim')->nullable()->after('layanan_kurir');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pesanan');
    }
};  