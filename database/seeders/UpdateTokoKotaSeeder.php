<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpdateTokoKotaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Update semua toko dengan kota asal Makassar
        // Kota ID 256 = Makassar (dari RajaOngkir)
        
        DB::table('toko')->update([
            'kota_id' => 256,
            'kota_asal' => 'Makassar'
        ]);
        
        $this->command->info('✅ Semua toko berhasil diupdate dengan kota asal Makassar');
    }
}
