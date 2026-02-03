<?php

namespace App\Http\Controllers;

use App\Services\RajaOngkirService;
use Illuminate\Http\Request;

class OngkirController extends Controller
{
    protected $rajaOngkir;

    public function __construct(RajaOngkirService $rajaOngkir)
    {
        $this->rajaOngkir = $rajaOngkir;
    }

    /**
     * Get all provinces
     */
    public function getProvinces()
    {
        $provinces = $this->rajaOngkir->getProvinces();

        return response()->json([
            'success' => !empty($provinces),
            'data' => $provinces
        ]);
    }

    /**
     * Get cities by province ID
     */
    public function getCities(Request $request)
    {
        $provinceId = $request->province_id;

        if (!$provinceId) {
            return response()->json([
                'success' => false,
                'message' => 'Province ID is required'
            ], 400);
        }

        $cities = $this->rajaOngkir->getCities($provinceId);

        return response()->json([
            'success' => !empty($cities),
            'data' => $cities
        ]);
    }

    /**
     * Get districts by city ID
     */
    public function getDistricts(Request $request)
    {
        $cityId = $request->city_id;

        if (!$cityId) {
            return response()->json([
                'success' => false,
                'message' => 'City ID is required'
            ], 400);
        }

        $districts = $this->rajaOngkir->getDistricts($cityId);

        return response()->json([
            'success' => !empty($districts),
            'data' => $districts
        ]);
    }

    /**
     * Check shipping cost
     * Courier format for V2: colon separated e.g. "jne:tiki:pos"
     */
    public function checkOngkir(Request $request)
    {
        $request->validate([
            'origin' => 'required',
            'destination' => 'required',
            'weight' => 'required|numeric',
            'courier' => 'required',
        ]);

        $results = $this->rajaOngkir->getCost(
            $request->origin,
            $request->destination,
            $request->weight,
            $request->courier
        );

        return response()->json([
            'success' => !empty($results),
            'data' => $results
        ]);
    }
}