<?php

namespace App\Http\Controllers;

use App\Models\Toko;
use App\Models\Produk;
use App\Models\Kategori;
use Illuminate\Http\Request;

class KatalogController extends Controller
{
    public function show($url_toko)
    {
        // Find toko by URL
        $toko = Toko::where('url_toko', $url_toko)->firstOrFail();
        
        // Get products with categories
        $products = Produk::where('user_id', $toko->user_id)
            ->with(['kategori', 'variants'])
            ->where('stok', '>', 0)
            ->orderBy('nama_produk')
            ->get();
        
        // Get categories
        $categories = Kategori::where('user_id', $toko->user_id)
            ->orderBy('nama_kategori')
            ->get();
        
        // Get origin city ID for shipping
        $originDistrictId = 6731;
        
        return view('katalog.index', compact('toko', 'products', 'categories', 'originDistrictId'));
    }
}
