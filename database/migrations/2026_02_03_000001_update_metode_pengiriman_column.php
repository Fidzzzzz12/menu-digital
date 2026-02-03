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
        // Update existing records to have default value
        DB::table('pesanan')
            ->whereNull('metode_pengiriman')
            ->orWhere('metode_pengiriman', '')
            ->update(['metode_pengiriman' => 'dikirim']);

        // Modify column to enum
        DB::statement("ALTER TABLE pesanan MODIFY COLUMN metode_pengiriman ENUM('dikirim', 'ambil_sendiri') NOT NULL DEFAULT 'dikirim'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to string
        Schema::table('pesanan', function (Blueprint $table) {
            $table->string('metode_pengiriman')->nullable()->change();
        });
    }
};