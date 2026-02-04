<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ClearOngkirCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ongkir:clear-cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear semua cache ongkir (provinces, cities, districts, dan cost calculations)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🗑️  Menghapus cache RajaOngkir...');

        // Hitung berapa banyak cache key yang dihapus
        $deleted = 0;

        // Clear cache dengan prefix tertentu
        // Note: Laravel Cache tidak bisa clear by prefix secara native,
        // jadi kita clear berdasarkan pattern yang kita tau
        
        $patterns = [
            'rajaongkir_v2_provinces',
            'rajaongkir_v2_cities_*',
            'rajaongkir_v2_districts_*',
            'ongkir_*',  // ini untuk cache cost calculations
        ];

        foreach ($patterns as $pattern) {
            if (strpos($pattern, '*') !== false) {
                // Untuk pattern dengan wildcard, kita perlu iterate
                // Ini hanya bekerja dengan driver 'file' atau 'redis'
                // Untuk simplicity, kita just flush all cache
                $this->warn("⚠️  Pattern wildcard terdeteksi. Akan flush seluruh cache.");
                Cache::flush();
                $this->info('✅ Semua cache berhasil dihapus!');
                return 0;
            } else {
                // Clear cache key spesifik
                if (Cache::has($pattern)) {
                    Cache::forget($pattern);
                    $deleted++;
                }
            }
        }

        if ($deleted > 0) {
            $this->info("✅ Berhasil menghapus {$deleted} cache key!");
        } else {
            $this->comment('ℹ️  Tidak ada cache yang perlu dihapus.');
        }

        return 0;
    }
}