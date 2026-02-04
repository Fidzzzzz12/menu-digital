<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Pesanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PesananController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $search = $request->input('search');
        
        $pesanan = Pesanan::where('user_id', $user->id)
            ->with('items.produk')
            ->when($search, function ($query, $search) {
                return $query->where('order_id', 'like', "%{$search}%")
                    ->orWhere('nama_lengkap', 'like', "%{$search}%");
            })
            ->orderBy('order_date', 'desc')
            ->get();
        
        // Count by status
        $pendingCount = Pesanan::where('user_id', $user->id)->where('status', 'pending')->count();
        $dikonfirmasiCount = Pesanan::where('user_id', $user->id)->where('status', 'dikonfirmasi')->count();
        $selesaiCount = Pesanan::where('user_id', $user->id)->where('status', 'selesai')->count();
        $dibatalkanCount = Pesanan::where('user_id', $user->id)->where('status', 'dibatalkan')->count();
        
        return view('pesanan.index', compact('pesanan', 'pendingCount', 'dikonfirmasiCount', 'selesaiCount', 'dibatalkanCount'));
    }
    
    public function konfirmasi($id)
    {
        $pesanan = Pesanan::where('user_id', Auth::id())->with('items.produk')->findOrFail($id);
        
        // Validasi stok sebelum konfirmasi
        foreach ($pesanan->items as $item) {
            $produk = $item->produk;
            if ($produk->stok < $item->quantity) {
                return back()->with('error', "Stok {$produk->nama_produk} tidak mencukupi! Stok tersedia: {$produk->stok}, dibutuhkan: {$item->quantity}");
            }
        }
        
        // Kurangi stok produk
        foreach ($pesanan->items as $item) {
            $produk = $item->produk;
            $produk->decrement('stok', $item->quantity);
        }
        
        $pesanan->update(['status' => 'dikonfirmasi']);
        
        // Generate WhatsApp message
        $message = $this->generateKonfirmasiMessage($pesanan);
        $waUrl = $this->generateWhatsAppUrl($pesanan->whatsapp, $message);
        
        return redirect($waUrl);
    }
    
    public function batalkan($id)
    {
        $pesanan = Pesanan::where('user_id', Auth::id())->with('items.produk')->findOrFail($id);
        
        // Kembalikan stok jika pesanan sudah dikonfirmasi
        if ($pesanan->status === 'dikonfirmasi') {
            foreach ($pesanan->items as $item) {
                $produk = $item->produk;
                $produk->increment('stok', $item->quantity);
            }
        }
        
        $pesanan->update(['status' => 'dibatalkan']);
        
        // Generate WhatsApp message
        $message = $this->generatePembatalanMessage($pesanan);
        $waUrl = $this->generateWhatsAppUrl($pesanan->whatsapp, $message);
        
        return redirect($waUrl);
    }
    
    public function selesai($id)
    {
        $pesanan = Pesanan::where('user_id', Auth::id())->findOrFail($id);
        $pesanan->update(['status' => 'selesai']);
        
        return back()->with('success', 'Pesanan berhasil diselesaikan! Pendapatan dashboard telah diupdate.');
    }
    
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,dikonfirmasi,selesai,dibatalkan'
        ]);
        
        $pesanan = Pesanan::where('user_id', Auth::id())->findOrFail($id);
        $pesanan->update(['status' => $request->status]);
        
        return back()->with('success', 'Status pesanan berhasil diupdate!');
    }
    
    private function generateKonfirmasiMessage($pesanan)
    {
        $user = Auth::user();
        $toko = $user->toko;
        
        $message = "Halo kak 👋\n\n";
        $message .= "Pesanan kakak sudah kami terima dan *dikonfirmasi*.\n\n";
        $message .= "ID Order : *{$pesanan->order_id}*\n";
        $message .= "Total    : *Rp" . number_format($pesanan->total_harga, 0, ',', '.') . "*\n";
        
        if ($pesanan->metode_pengiriman === 'dikirim') {
            $message .= "Metode   : *Dikirim";
            if ($pesanan->kurir) {
                $message .= " (" . strtoupper($pesanan->kurir) . ")";
            }
            $message .= "*\n\n";
            $message .= "Pesanan sedang kami siapkan dan akan kami kirimkan\n";
            $message .= "setelah proses pengemasan selesai.\n\n";
            $message .= "Nomor resi akan kami informasikan setelah pengiriman.\n\n";
        } else {
            $message .= "Metode   : *Ambil di toko*\n\n";
            $message .= "Pesanan sedang kami siapkan.\n";
            $message .= "Kami akan mengabari kakak kembali jika sudah siap diambil.\n\n";
        }
        
        $message .= "Terima kasih 🙏";
        
        return $message;
    }
    
    private function generatePembatalanMessage($pesanan)
    {
        $user = Auth::user();
        $toko = $user->toko;
        
        $message = "Halo kak 👋\n\n";
        $message .= "Mohon maaf, pesanan dengan ID *{$pesanan->order_id}* terpaksa kami *batalkan*.\n\n";
        $message .= "Alasannya karena *stok produk saat ini tidak tersedia*.\n\n";
        $message .= "Kami mohon maaf atas ketidaknyamanannya.\n";
        $message .= "Silakan hubungi kami jika ingin memesan produk lain.\n\n";
        $message .= "Terima kasih atas pengertiannya 🙏";
        
        return $message;
    }
    
    private function generateWhatsAppUrl($phone, $message)
    {
        // Format phone number
        $phone = preg_replace('/\D/', '', $phone);
        if (substr($phone, 0, 1) === '0') {
            $phone = '62' . substr($phone, 1);
        }
        
        return 'https://wa.me/' . $phone . '?text=' . urlencode($message);
    }
}
