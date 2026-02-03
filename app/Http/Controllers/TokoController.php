<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateTokoRequest;
use App\Models\Toko;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TokoController extends Controller
{
    /**
     * Get toko information
     */
    public function show()
    {
        $toko = Auth::user()->toko;

        if (!$toko) {
            return response()->json([
                'success' => false,
                'message' => 'Toko not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'toko' => $toko,
        ]);
    }

    /**
     * Update toko information
     */
    public function update(UpdateTokoRequest $request)
    {
        $toko = Auth::user()->toko;
        
        if (!$toko) {
            return response()->json([
                'success' => false,
                'message' => 'Toko not found',
            ], 404);
        }

        $validated = $request->validated();
        $toko->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Toko updated successfully',
            'toko' => $toko->fresh(),
        ]);
    }

    /**
     * Upload foto profil
     */
    public function uploadFotoProfil(Request $request)
    {
        $request->validate([
            'foto_profil' => 'required|string', // Base64 string
        ]);

        $toko = Auth::user()->toko;
        $toko->update([
            'foto_profil' => $request->foto_profil,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Foto profil uploaded successfully',
            'foto_profil' => $toko->foto_profil,
        ]);
    }

    /**
     * Delete foto profil
     */
    public function deleteFotoProfil()
    {
        $toko = Auth::user()->toko;
        $toko->update([
            'foto_profil' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Foto profil deleted successfully',
        ]);
    }

    /**
     * Upload banner toko
     */
    public function uploadBanner(Request $request)
    {
        $request->validate([
            'banner_toko' => 'required|string', // Base64 string
            'banner_position' => 'nullable|array',
            'banner_zoom' => 'nullable|integer|min:10|max:200',
        ]);

        $toko = Auth::user()->toko;
        $toko->update([
            'banner_toko' => $request->banner_toko,
            'banner_position' => $request->banner_position,
            'banner_zoom' => $request->banner_zoom ?? 100,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Banner uploaded successfully',
            'banner' => [
                'banner_toko' => $toko->banner_toko,
                'banner_position' => $toko->banner_position,
                'banner_zoom' => $toko->banner_zoom,
            ],
        ]);
    }

    /**
     * Delete banner
     */
    public function deleteBanner()
    {
        $toko = Auth::user()->toko;
        $toko->update([
            'banner_toko' => null,
            'banner_position' => null,
            'banner_zoom' => 100,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Banner deleted successfully',
        ]);
    }

    /**
     * Get toko URL for sharing
     */
    public function getShareUrl()
    {
        $user = Auth::user();
        $url = config('app.url') . '/katalog/' . $user->url_toko;

        return response()->json([
            'success' => true,
            'url' => $url,
            'url_toko' => $user->url_toko,
        ]);
    }

    /**
     * Get public toko data by URL (for customers)
     */
    public function getByUrl($urlToko)
    {
        $user = User::where('url_toko', $urlToko)->first();

        if (!$user || !$user->toko) {
            return response()->json([
                'success' => false,
                'message' => 'Toko not found',
            ], 404);
        }

        $toko = $user->toko;
        $toko->load(['kategori', 'produk.variants']);

        return response()->json([
            'success' => true,
            'toko' => $toko,
        ]);
    }
}