<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Kategori;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KategoriController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $categories = Kategori::where('user_id', $user->id)
            ->withCount('produk as jumlah_produk')
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('kategori.index', compact('categories'));
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'nama_kategori' => 'required|string|max:255',
        ]);
        
        Kategori::create([
            'user_id' => Auth::id(),
            'nama_kategori' => $request->nama_kategori,
        ]);
        
        return back()->with('success', 'Kategori berhasil ditambahkan!');
    }
    
    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_kategori' => 'required|string|max:255',
        ]);
        
        $kategori = Kategori::where('user_id', Auth::id())
            ->findOrFail($id);
        
        $kategori->update([
            'nama_kategori' => $request->nama_kategori,
        ]);
        
        return back()->with('success', 'Kategori berhasil diupdate!');
    }
    
    public function destroy($id)
    {
        $kategori = Kategori::where('user_id', Auth::id())
            ->findOrFail($id);
        
        // Check if kategori has products
        if ($kategori->produk()->count() > 0) {
            return back()->with('error', 'Kategori tidak bisa dihapus karena masih memiliki produk!');
        }
        
        $kategori->delete();
        
        return back()->with('success', 'Kategori berhasil dihapus!');
    }
}
