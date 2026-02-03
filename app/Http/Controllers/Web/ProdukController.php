<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use App\Models\Kategori;
use App\Models\ProdukVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProdukController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        $products = Produk::where('user_id', $user->id)
            ->with(['kategori', 'variants'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        $categories = Kategori::where('user_id', $user->id)
            ->orderBy('nama_kategori')
            ->get();
        
        return view('produk.index', compact('products', 'categories'));
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'nama_produk' => 'required|string|max:255',
            'kategori_id' => 'required|exists:kategori,id',
            'harga' => 'required|numeric|min:0',
            'stok' => 'required|integer|min:0',
            'deskripsi' => 'nullable|string',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'has_variants' => 'nullable',
            'variants.*.nama' => 'nullable|string|max:255',
            'variants.*.harga_tambahan' => 'nullable|numeric',
        ]);
        
        // Handle image upload
        $gambarPath = null;
        if ($request->hasFile('gambar')) {
            $gambarPath = $request->file('gambar')->store('produk', 'public');
        }
        
        // Create product
        $produk = Produk::create([
            'user_id' => Auth::id(),
            'kategori_id' => $request->kategori_id,
            'nama_produk' => $request->nama_produk,
            'harga' => $request->harga,
            'stok' => $request->stok,
            'deskripsi' => $request->deskripsi,
            'gambar' => $gambarPath,
        ]);
        
        // Create variants if exists
        if ($request->has('has_variants') && $request->variants) {
            foreach ($request->variants as $variant) {
                if (!empty($variant['nama'])) {
                    ProdukVariant::create([
                        'produk_id' => $produk->id,
                        'nama_variant' => $variant['nama'],
                        'harga_tambahan' => $variant['harga_tambahan'] ?? 0,
                    ]);
                }
            }
        }
        
        return back()->with('success', 'Produk berhasil ditambahkan!');
    }
    
    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_produk' => 'required|string|max:255',
            'kategori_id' => 'required|exists:kategori,id',
            'harga' => 'required|numeric|min:0',
            'stok' => 'required|integer|min:0',
            'deskripsi' => 'nullable|string',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'has_variants' => 'nullable',
            'variants.*.nama' => 'nullable|string|max:255',
            'variants.*.harga_tambahan' => 'nullable|numeric',
        ]);
        
        $produk = Produk::where('user_id', Auth::id())
            ->findOrFail($id);
        
        // Handle image upload
        if ($request->hasFile('gambar')) {
            // Delete old image
            if ($produk->gambar) {
                Storage::disk('public')->delete($produk->gambar);
            }
            
            $gambarPath = $request->file('gambar')->store('produk', 'public');
            $produk->gambar = $gambarPath;
        }
        
        // Update product
        $produk->update([
            'kategori_id' => $request->kategori_id,
            'nama_produk' => $request->nama_produk,
            'harga' => $request->harga,
            'stok' => $request->stok,
            'deskripsi' => $request->deskripsi,
        ]);
        
        // Update variants
        if ($request->has('has_variants') && $request->variants) {
            // Delete old variants
            $produk->variants()->delete();
            
            foreach ($request->variants as $variant) {
                if (!empty($variant['nama'])) {
                    ProdukVariant::create([
                        'produk_id' => $produk->id,
                        'nama_variant' => $variant['nama'],
                        'harga_tambahan' => $variant['harga_tambahan'] ?? 0,
                    ]);
                }
            }
        } else {
            // Remove all variants if has_variants is false
            $produk->variants()->delete();
        }
        
        return back()->with('success', 'Produk berhasil diupdate!');
    }
    
    public function destroy($id)
    {
        $produk = Produk::where('user_id', Auth::id())
            ->findOrFail($id);
        
        // Delete image
        if ($produk->gambar) {
            Storage::disk('public')->delete($produk->gambar);
        }
        
        // Delete variants
        $produk->variants()->delete();
        
        // Delete product
        $produk->delete();
        
        return back()->with('success', 'Produk berhasil dihapus!');
    }
}
