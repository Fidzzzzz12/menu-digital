<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Toko;
use App\Models\Kategori;
use App\Models\Produk;
use Illuminate\Http\Request;

class KatalogApiController extends Controller
{
    public function show($url_toko)
    {
        try {
            // Find toko by URL
            $toko = Toko::where('url_toko', $url_toko)->first();
            
            if (!$toko) {
                return response()->json([
                    'success' => false,
                    'message' => 'Toko tidak ditemukan'
                ], 404);
            }

            // Get categories with products
            $kategori = Kategori::where('user_id', $toko->user_id)
                ->with(['produk' => function($query) {
                    $query->where('status', 'aktif');
                }])
                ->get();

            // Get all products
            $products = Produk::where('user_id', $toko->user_id)
                ->where('status', 'aktif')
                ->with('kategori')
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'toko' => $toko,
                    'kategori' => $kategori,
                    'products' => $products,
                    'originDistrictId' => $toko->kecamatan_id ?? 1391
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}