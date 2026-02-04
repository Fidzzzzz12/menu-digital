<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class RajaOngkirService
{
    protected $apiKey;
    protected $baseUrl = 'https://rajaongkir.komerce.id/api/v1';

    public function __construct()
    {
        $this->apiKey = config('rajaongkir.api_key');
    }

    public function getProvinces()
    {
        return Cache::remember('rajaongkir_v2_provinces', 86400, function () {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'key' => $this->apiKey,
            ])->get("{$this->baseUrl}/destination/province");

            if ($response->successful()) {
                return $response->json()['data'] ?? [];
            }

            Log::error('RajaOngkir getProvinces gagal', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            return [];
        });
    }

    public function getCities($provinceId)
    {
        return Cache::remember('rajaongkir_v2_cities_' . $provinceId, 86400, function () use ($provinceId) {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'key' => $this->apiKey,
            ])->get("{$this->baseUrl}/destination/city/{$provinceId}");

            if ($response->successful()) {
                return $response->json()['data'] ?? [];
            }

            Log::error('RajaOngkir getCities gagal', [
                'province_id' => $provinceId,
                'status'      => $response->status(),
                'body'        => $response->body(),
            ]);

            return [];
        });
    }

    public function getDistricts($cityId)
    {
        return Cache::remember('rajaongkir_v2_districts_' . $cityId, 86400, function () use ($cityId) {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'key' => $this->apiKey,
            ])->get("{$this->baseUrl}/destination/district/{$cityId}");

            if ($response->successful()) {
                return $response->json()['data'] ?? [];
            }

            Log::error('RajaOngkir getDistricts gagal', [
                'city_id' => $cityId,
                'status'  => $response->status(),
                'body'    => $response->body(),
            ]);

            return [];
        });
    }

    /**
     * Calculate cost dengan CACHE 24 jam.
     * 
     * Cache Key Format: ongkir_{origin}_{destination}_{weight}_{courier}
     * Contoh: ongkir_6731_6740_500_jne
     * 
     * Durasi: 86400 detik = 24 jam
     * Setelah 24 jam, cache otomatis dihapus dan request berikutnya akan tanya API lagi.
     */
    public function getCost($origin, $destination, $weight, $courier)
    {
        // Buat cache key unik berdasarkan kombinasi parameter
        $cacheKey = "ongkir_{$origin}_{$destination}_{$weight}_{$courier}";

        // Cek apakah sudah ada di cache
        return Cache::remember($cacheKey, 86400, function () use ($origin, $destination, $weight, $courier) {
            $payload = [
                'origin'      => $origin,
                'destination' => $destination,
                'weight'      => $weight,
                'courier'     => $courier,
                'price'       => 'lowest',
            ];

            Log::info('RajaOngkir getCost REQUEST (calling API - not from cache)', $payload);

            try {
                // PENTING: API V2 butuh Content-Type: application/x-www-form-urlencoded
                $response = Http::asForm()->withHeaders([
                    'Accept' => 'application/json',
                    'key'    => $this->apiKey,
                ])->post("{$this->baseUrl}/calculate/district/domestic-cost", $payload);

                // Log full response
                Log::info('RajaOngkir getCost RESPONSE', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);

                if ($response->successful()) {
                    return $response->json()['data'] ?? [];
                }

                // Jangan cache kalau gagal - return [] dan jangan disimpan
                return [];
            } catch (\Exception $e) {
                Log::error('RajaOngkir getCost EXCEPTION: ' . $e->getMessage());
                return [];
            }
        });
    }
}