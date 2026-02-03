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
     * Calculate cost.
     * Selalu log request + response supaya gampang debug.
     */
    public function getCost($origin, $destination, $weight, $courier)
    {
        $payload = [
            'origin'      => $origin,
            'destination' => $destination,
            'weight'      => $weight,
            'courier'     => $courier,
            'price'       => 'lowest',
        ];

        Log::info('RajaOngkir getCost REQUEST', $payload);

        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'key'    => $this->apiKey,
            ])->post("{$this->baseUrl}/calculate/district/domestic-cost", $payload);

            // Log full response — apapun status-nya
            Log::info('RajaOngkir getCost RESPONSE', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            if ($response->successful()) {
                return $response->json()['data'] ?? [];
            }

            return [];
        } catch (\Exception $e) {
            Log::error('RajaOngkir getCost EXCEPTION: ' . $e->getMessage());
            return [];
        }
    }
}