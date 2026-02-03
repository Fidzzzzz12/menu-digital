<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProdukRequest;
use App\Http\Requests\UpdateProdukRequest;
use App\Models\Produk;
use App\Models\ProdukVariant;
use App\Models\Kategori;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProdukController extends Controller
{
    /**
     * Display a listing of produk
     */
    public function index(Request $request)
    {
        $tokoId = Auth::user()->toko->id;
        $search = $request->query('search');
        $kategoriId = $request->query('kategori_id');

        $query = Produk::where('toko_id', $tokoId);

        if ($search) {
            $query->search($search);
        }

        if ($kategoriId) {
            $query->byCategory($kategoriId);
        }

        $produk = $query->with(['kategori', 'variants'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'produk' => $produk,
        ]);
    }

    /**
     * Store a newly created produk
     */
    public function store(StoreProdukRequest $request)
    {
        $validated = $request->validated();
        $tokoId = Auth::user()->toko->id;

        DB::beginTransaction();
        try {
            $produk = Produk::create([
                'toko_id' => $tokoId,
                'kategori_id' => $validated['kategori_id'],
                'name' => $validated['name'],
                'price' => $validated['price'],
                'stock' => $validated['stock'] ?? 0,
                'description' => $validated['description'] ?? null,
                'image' => $validated['image'] ?? null,
                'has_variants' => isset($validated['variants']) && count($validated['variants']) > 0,
            ]);

            // Add variants if exists
            if (isset($validated['variants']) && count($validated['variants']) > 0) {
                foreach ($validated['variants'] as $variant) {
                    ProdukVariant::create([
                        'produk_id' => $produk->id,
                        'name' => $variant['name'],
                        'price' => $variant['price'],
                        'image' => $variant['image'] ?? null,
                    ]);
                }
            }

            // Update kategori product count
            if ($produk->kategori) {
                $produk->kategori->updateProductCount();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Produk created successfully',
                'produk' => $produk->load('variants'),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create produk: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified produk
     */
    public function show($id)
    {
        $tokoId = Auth::user()->toko->id;
        $produk = Produk::where('toko_id', $tokoId)->with(['kategori', 'variants'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'produk' => $produk,
        ]);
    }

    /**
     * Update the specified produk
     */
    public function update(UpdateProdukRequest $request, $id)
    {
        $tokoId = Auth::user()->toko->id;
        $produk = Produk::where('toko_id', $tokoId)->findOrFail($id);
        $validated = $request->validated();

        DB::beginTransaction();
        try {
            $oldKategoriId = $produk->kategori_id;

            $produk->update([
                'kategori_id' => $validated['kategori_id'] ?? $produk->kategori_id,
                'name' => $validated['name'] ?? $produk->name,
                'price' => $validated['price'] ?? $produk->price,
                'stock' => $validated['stock'] ?? $produk->stock,
                'description' => $validated['description'] ?? $produk->description,
                'image' => $validated['image'] ?? $produk->image,
                'has_variants' => isset($validated['variants']) && count($validated['variants']) > 0,
            ]);

            // Update variants
            if (isset($validated['variants'])) {
                // Delete old variants
                $produk->variants()->delete();
                
                // Add new variants
                foreach ($validated['variants'] as $variant) {
                    ProdukVariant::create([
                        'produk_id' => $produk->id,
                        'name' => $variant['name'],
                        'price' => $variant['price'],
                        'image' => $variant['image'] ?? null,
                    ]);
                }
            }

            // Update kategori product count for both old and new kategori
            if ($oldKategoriId && $oldKategoriId != $produk->kategori_id) {
                $oldKategori = Kategori::find($oldKategoriId);
                if ($oldKategori) {
                    $oldKategori->updateProductCount();
                }
            }

            if ($produk->kategori) {
                $produk->kategori->updateProductCount();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Produk updated successfully',
                'produk' => $produk->fresh(['kategori', 'variants']),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update produk: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified produk
     */
    public function destroy($id)
    {
        $tokoId = Auth::user()->toko->id;
        $produk = Produk::where('toko_id', $tokoId)->findOrFail($id);

        $kategori = $produk->kategori;
        $produk->delete();

        // Update kategori product count
        if ($kategori) {
            $kategori->updateProductCount();
        }

        return response()->json([
            'success' => true,
            'message' => 'Produk deleted successfully',
        ]);
    }

    /**
     * Search produk
     */
    public function search(Request $request)
    {
        $tokoId = Auth::user()->toko->id;
        $search = $request->query('q');

        $produk = Produk::where('toko_id', $tokoId)
            ->search($search)
            ->with(['kategori', 'variants'])
            ->get();

        return response()->json([
            'success' => true,
            'produk' => $produk,
        ]);
    }

    /**
     * Filter produk by kategori
     */
    public function filterByKategori($kategoriId)
    {
        $tokoId = Auth::user()->toko->id;

        $produk = Produk::where('toko_id', $tokoId)
            ->byCategory($kategoriId)
            ->with(['kategori', 'variants'])
            ->get();

        return response()->json([
            'success' => true,
            'produk' => $produk,
        ]);
    }

    /**
     * Upload product image
     */
    public function uploadImage(Request $request, $id)
    {
        $request->validate([
            'image' => 'required|string', // Base64 string
        ]);

        $tokoId = Auth::user()->toko->id;
        $produk = Produk::where('toko_id', $tokoId)->findOrFail($id);

        $produk->update([
            'image' => $request->image,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Image uploaded successfully',
            'image' => $produk->image,
        ]);
    }
}