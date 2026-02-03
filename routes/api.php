<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TokoController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\PesananController;
use App\Http\Controllers\DashboardController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/check-auth', [AuthController::class, 'checkAuth']);

// Public toko route (for customers)
Route::get('/katalog/{url_toko}', [TokoController::class, 'getByUrl']);

// Public pesanan route (for customers to create order)
Route::post('/pesanan/create', [\App\Http\Controllers\Api\PesananApiController::class, 'store']);

// Protected routes (require authentication with token)
Route::middleware('auth:sanctum')->group(function () {
    
    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Toko routes
    Route::prefix('toko')->group(function () {
        Route::get('/', [TokoController::class, 'show']);
        Route::put('/', [TokoController::class, 'update']);
        Route::post('/foto-profil', [TokoController::class, 'uploadFotoProfil']);
        Route::delete('/foto-profil', [TokoController::class, 'deleteFotoProfil']);
        Route::post('/banner', [TokoController::class, 'uploadBanner']);
        Route::delete('/banner', [TokoController::class, 'deleteBanner']);
        Route::get('/share-url', [TokoController::class, 'getShareUrl']);
    });
    
    // Dashboard routes
    Route::prefix('dashboard')->group(function () {
        Route::get('/statistics', [DashboardController::class, 'getStatistics']);
        Route::get('/chart-data', [DashboardController::class, 'getChartData']);
        Route::get('/yearly-overview', [DashboardController::class, 'getYearlyOverview']);
        Route::post('/filter-by-month', [DashboardController::class, 'filterByMonth']);
    });
    
    // Kategori routes
    Route::prefix('kategori')->group(function () {
        Route::get('/', [KategoriController::class, 'index']);
        Route::post('/', [KategoriController::class, 'store']);
        Route::get('/{id}', [KategoriController::class, 'show']);
        Route::put('/{id}', [KategoriController::class, 'update']);
        Route::delete('/{id}', [KategoriController::class, 'destroy']);
        Route::get('/search/query', [KategoriController::class, 'search']);
    });
    
    // Produk routes
    Route::prefix('produk')->group(function () {
        Route::get('/', [ProdukController::class, 'index']);
        Route::post('/', [ProdukController::class, 'store']);
        Route::get('/{id}', [ProdukController::class, 'show']);
        Route::put('/{id}', [ProdukController::class, 'update']);
        Route::delete('/{id}', [ProdukController::class, 'destroy']);
        Route::get('/search/query', [ProdukController::class, 'search']);
        Route::get('/kategori/{kategori_id}', [ProdukController::class, 'filterByKategori']);
        Route::post('/{id}/upload-image', [ProdukController::class, 'uploadImage']);
    });
    
    // Pesanan routes (for toko owner)
    Route::prefix('pesanan')->group(function () {
        Route::get('/', [PesananController::class, 'index']);
        Route::get('/{id}', [PesananController::class, 'show']);
        Route::put('/{id}/status', [PesananController::class, 'updateStatus']);
        Route::get('/search/query', [PesananController::class, 'search']);
    });
    
    // Get authenticated user
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});         