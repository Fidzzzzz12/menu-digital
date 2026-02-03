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
        Schema::create('toko', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('nama_lengkap', 255)->nullable();
            $table->string('nama_toko', 255)->notNullable();
            $table->string('url_toko', 255)->unique()->notNullable();
            $table->string('email', 255)->notNullable();
            $table->string('nomor_telepon', 20)->notNullable();
            $table->text('alamat')->nullable();
            $table->text('deskripsi')->nullable();
            $table->text('foto_profil')->nullable()->comment('Base64/URL');
            $table->text('banner_toko')->nullable()->comment('Base64/URL');
            $table->json('banner_position')->nullable()->comment('{x, y}');
            $table->integer('banner_zoom')->default(100);
            $table->timestamps();

            // Index
            $table->index('user_id', 'idx_toko_user_id');
            $table->index('url_toko', 'idx_toko_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('toko');
    }
};