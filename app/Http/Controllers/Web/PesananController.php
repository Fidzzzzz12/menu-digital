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
        $pesanan = Pesanan::where('user_id', Auth::id())->findOrFail($id);
        $pesanan->update(['status' => 'dikonfirmasi']);
        
        // Generate WhatsApp message
        $message = $this->generateKonfirmasiMessage($pesanan);
        $waUrl = $this->generateWhatsAppUrl($pesanan->whatsapp, $message);
        
        return redirect($waUrl);
    }
    
    public function batalkan($id)
    {
        $pesanan = Pesanan::where('user_id', Auth::id())->findOrFail($id);
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
        
        $message = "Halo {$pesanan->nama_lengkap}, terima kasih atas pesanan Anda!\n\n";
        $message .= "ID Pesanan: *{$pesanan->order_id}*\n\n";
        $message .= "*Detail Pesanan:*\n";
        
        foreach ($pesanan->items as $index => $item) {
            $no = $index + 1;
            $variant = $item->variant ? " ({$item->variant})" : '';
            $message .= "{$no}. {$item->nama_produk}{$variant}\n";
            $message .= "   {$item->quantity}x Rp" . number_format($item->harga, 0, ',', '.') . " = Rp" . number_format($item->subtotal, 0, ',', '.') . "\n";
        }
        
        $message .= "\n*Total: Rp" . number_format($pesanan->total_harga, 0, ',', '.') . "*\n\n";
        $message .= "Pesanan Anda sedang diproses. Kami akan menghubungi Anda segera.\n\n";
        $message .= "Terima kasih,\n";
        $message .= $toko->nama_toko ?? 'Toko Kami';
        
        return $message;
    }
    
    private function generatePembatalanMessage($pesanan)
    {
        $user = Auth::user();
        $toko = $user->toko;
        
        $message = "Halo {$pesanan->nama_lengkap}, mohon maaf pesanan Anda tidak dapat diproses.\n\n";
        $message .= "ID Pesanan: *{$pesanan->order_id}*\n\n";
        $message .= "Alasan: Stok tidak tersedia / Toko sedang tutup\n\n";
        $message .= "Mohon maaf atas ketidaknyamanannya.\n\n";
        $message .= "Terima kasih,\n";
        $message .= $toko->nama_toko ?? 'Toko Kami';
        
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
