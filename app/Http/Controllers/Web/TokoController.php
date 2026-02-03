<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Toko;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TokoController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Refresh user data from database
        $user->refresh();
        
        $toko = Toko::where('user_id', $user->id)->first();
        
        if (!$toko) {
            // Create toko if not exists
            $urlToko = $user->url_toko ?? $this->generateUrlToko($user->nama_toko ?? 'toko');
            
            $toko = Toko::create([
                'user_id' => $user->id,
                'nama_toko' => $user->nama_toko ?? 'Toko Saya',
                'url_toko' => $urlToko,
                'email' => $user->email,
                'nomor_telepon' => $user->nomor_telepon ?? '',
            ]);
        }
        
        return view('setting.index', compact('toko', 'user'));
    }
    
    /**
     * Generate URL toko dari nama toko
     */
    private function generateUrlToko($namaToko)
    {
        // Convert to lowercase and replace spaces with hyphens
        $url = strtolower($namaToko);
        $url = preg_replace('/[^a-z0-9-]/', '-', $url);
        $url = preg_replace('/-+/', '-', $url);
        $url = trim($url, '-');
        
        // Check if URL already exists
        $originalUrl = $url;
        $counter = 1;
        
        while (Toko::where('url_toko', $url)->exists()) {
            $url = $originalUrl . '-' . $counter;
            $counter++;
        }
        
        return $url;
    }
    
    public function update(Request $request)
    {
        $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'nama_toko' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . Auth::id(),
            'nomor_telepon' => 'required|string|max:20',
            'alamat' => 'nullable|string',
            'deskripsi' => 'nullable|string',
        ]);
        
        $toko = Toko::where('user_id', Auth::id())->first();
        
        // Update toko data
        $toko->update([
            'nama_lengkap' => $request->nama_lengkap,
            'nama_toko' => $request->nama_toko,
            'email' => $request->email,
            'nomor_telepon' => $request->nomor_telepon,
            'alamat' => $request->alamat,
            'deskripsi' => $request->deskripsi,
        ]);
        
        // Update user data
        $user = Auth::user();
        $user->email = $request->email;
        $user->nama_toko = $request->nama_toko;
        $user->nomor_telepon = $request->nomor_telepon;
        $user->save();
        
        // Refresh auth user
        Auth::setUser($user);
        
        return redirect()->route('setting.index')->with('success', 'Data toko berhasil diupdate!');
    }
    
    public function uploadFotoProfil(Request $request)
    {
        $request->validate([
            'foto_profil' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);
        
        $toko = Toko::where('user_id', Auth::id())->first();
        
        // Delete old photo
        if ($toko->foto_profil) {
            Storage::disk('public')->delete($toko->foto_profil);
        }
        
        // Upload new photo
        $path = $request->file('foto_profil')->store('toko/profil', 'public');
        
        $toko->update([
            'foto_profil' => $path,
        ]);
        
        return back()->with('success', 'Foto profil berhasil diupload!');
    }
    
    public function uploadBanner(Request $request)
    {
        $request->validate([
            'banner' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);
        
        $toko = Toko::where('user_id', Auth::id())->first();
        
        // Delete old banner
        if ($toko->banner_toko) {
            Storage::disk('public')->delete($toko->banner_toko);
        }
        
        // Upload new banner
        $path = $request->file('banner')->store('toko/banner', 'public');
        
        $toko->update([
            'banner_toko' => $path,
        ]);
        
        return back()->with('success', 'Banner berhasil diupload!');
    }
    
    public function hapusFotoProfil()
    {
        $toko = Toko::where('user_id', Auth::id())->first();
        
        if ($toko->foto_profil) {
            Storage::disk('public')->delete($toko->foto_profil);
            $toko->update(['foto_profil' => null]);
        }
        
        return back()->with('success', 'Foto profil berhasil dihapus!');
    }
    
    public function hapusBanner()
    {
        $toko = Toko::where('user_id', Auth::id())->first();
        
        if ($toko->banner_toko) {
            Storage::disk('public')->delete($toko->banner_toko);
            $toko->update(['banner_toko' => null]);
        }
        
        return back()->with('success', 'Banner berhasil dihapus!');
    }
}
