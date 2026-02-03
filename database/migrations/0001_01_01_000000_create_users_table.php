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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('email', 255)->unique()->notNullable();
            $table->string('password', 255)->notNullable();
            $table->string('nama_toko', 255)->notNullable();
            $table->string('url_toko', 100)->unique()->notNullable();
            $table->string('nomor_telepon', 20)->notNullable();
            $table->timestamp('registered_at')->useCurrent();
            $table->timestamps();

            // Index
            $table->index('email', 'idx_users_email');
            $table->index('url_toko', 'idx_users_url_toko');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};