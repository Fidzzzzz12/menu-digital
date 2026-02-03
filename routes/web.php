<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\KategoriController;
use App\Http\Controllers\Web\ProdukController;
use App\Http\Controllers\Web\TokoController;
use App\Http\Controllers\Web\PesananController;
use App\Http\Controllers\KatalogController;
use App\Http\Controllers\OngkirController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// API Routes untuk RajaOngkir
Route::get('/api/provinces', [OngkirController::class, 'getProvinces']);
Route::get('/api/cities', [OngkirController::class, 'getCities']);
Route::get('/api/districts', [OngkirController::class, 'getDistricts']);   // <-- ini yang baru
Route::post('/api/check-ongkir', [OngkirController::class, 'checkOngkir']);


// Public Katalog Route (no auth required)
Route::get('/katalog/{url_toko}', [KatalogController::class, 'show'])->name('katalog.show');

// Guest routes (not authenticated)
Route::middleware('guest')->group(function () {
    Route::get('/', function () {
        return redirect()->route('login');
    });
    
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Kategori
    Route::get('/kategori', [KategoriController::class, 'index'])->name('kategori.index');
    Route::post('/kategori', [KategoriController::class, 'store'])->name('kategori.store');
    Route::put('/kategori/{id}', [KategoriController::class, 'update'])->name('kategori.update');
    Route::delete('/kategori/{id}', [KategoriController::class, 'destroy'])->name('kategori.destroy');
    
    // Produk
    Route::get('/produk', [ProdukController::class, 'index'])->name('produk.index');
    Route::post('/produk', [ProdukController::class, 'store'])->name('produk.store');
    Route::put('/produk/{id}', [ProdukController::class, 'update'])->name('produk.update');
    Route::delete('/produk/{id}', [ProdukController::class, 'destroy'])->name('produk.destroy');
    
    // Pesanan
    Route::get('/pesanan', [PesananController::class, 'index'])->name('pesanan.index');
    Route::put('/pesanan/{id}/status', [PesananController::class, 'updateStatus'])->name('pesanan.updateStatus');
    Route::post('/pesanan/{id}/konfirmasi', [PesananController::class, 'konfirmasi'])->name('pesanan.konfirmasi');
    Route::post('/pesanan/{id}/batalkan', [PesananController::class, 'batalkan'])->name('pesanan.batalkan');
    Route::post('/pesanan/{id}/selesai', [PesananController::class, 'selesai'])->name('pesanan.selesai');
    
    // Setting/Toko
    Route::get('/setting', [TokoController::class, 'index'])->name('setting.index');
    Route::put('/setting', [TokoController::class, 'update'])->name('setting.update');
    Route::post('/setting/foto-profil', [TokoController::class, 'uploadFotoProfil'])->name('setting.foto-profil');
    Route::post('/setting/banner', [TokoController::class, 'uploadBanner'])->name('setting.banner');
    Route::delete('/setting/foto-profil', [TokoController::class, 'hapusFotoProfil'])->name('setting.hapus-foto-profil');
    Route::delete('/setting/banner', [TokoController::class, 'hapusBanner'])->name('setting.hapus-banner');
});
