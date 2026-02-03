<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreKategoriRequest;
use App\Http\Requests\UpdateKategoriRequest;
use App\Models\Kategori;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KategoriController extends Controller
{
    /**
     * Display a listing of kategori
     */
    public function index(Request $request)
    {
        $tokoId = Auth::user()->toko->id;
        $search = $request->query('search');

        $query = Kategori::where('toko_id', $tokoId);

        if ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        $kategori = $query->withCount('produk')->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'kategori' => $kategori,
        ]);
    }

    /**
     * Store a newly created kategori
     */
    public function store(StoreKategoriRequest $request)
    {
        $validated = $request->validated();
        $tokoId = Auth::user()->toko->id;

        $kategori = Kategori::create([
            'toko_id' => $tokoId,
            'name' => $validated['name'],
            'product_count' => 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Kategori created successfully',
            'kategori' => $kategori,
        ], 201);
    }

    /**
     * Display the specified kategori
     */
    public function show($id)
    {
        $tokoId = Auth::user()->toko->id;
        $kategori = Kategori::where('toko_id', $tokoId)->findOrFail($id);

        $kategori->load('produk');

        return response()->json([
            'success' => true,
            'kategori' => $kategori,
        ]);
    }

    /**
     * Update the specified kategori
     */
    public function update(UpdateKategoriRequest $request, $id)
    {
        $tokoId = Auth::user()->toko->id;
        $kategori = Kategori::where('toko_id', $tokoId)->findOrFail($id);

        $validated = $request->validated();
        $kategori->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Kategori updated successfully',
            'kategori' => $kategori->fresh(),
        ]);
    }

    /**
     * Remove the specified kategori
     */
    public function destroy($id)
    {
        $tokoId = Auth::user()->toko->id;
        $kategori = Kategori::where('toko_id', $tokoId)->findOrFail($id);

        // Check if kategori has products
        if ($kategori->produk()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete kategori with existing products',
            ], 400);
        }

        $kategori->delete();

        return response()->json([
            'success' => true,
            'message' => 'Kategori deleted successfully',
        ]);
    }

    /**
     * Search kategori
     */
    public function search(Request $request)
    {
        $tokoId = Auth::user()->toko->id;
        $search = $request->query('q');

        $kategori = Kategori::where('toko_id', $tokoId)
            ->where('name', 'like', '%' . $search . '%')
            ->withCount('produk')
            ->get();

        return response()->json([
            'success' => true,
            'kategori' => $kategori,
        ]);
    }
}