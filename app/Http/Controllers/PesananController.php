<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePesananRequest;
use App\Models\Pesanan;
use App\Models\PesananItem;
use App\Models\Toko;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PesananController extends Controller
{
    /**
     * Display a listing of pesanan
     */
    public function index(Request $request)
    {
        $tokoId = Auth::user()->toko->id;
        $search = $request->query('search');
        $status = $request->query('status');

        $query = Pesanan::where('toko_id', $tokoId);

        if ($search) {
            $query->search($search);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $pesanan = $query->with('items')
            ->orderBy('order_date', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'pesanan' => $pesanan,
        ]);
    }

    /**
     * Store a newly created pesanan (from customer)
     */
    public function store(StorePesananRequest $request)
    {
        $validated = $request->validated();

        // Get toko by URL
        $user = User::where('url_toko', $validated['url_toko'])->first();
        
        if (!$user || !$user->toko) {
            return response()->json([
                'success' => false,
                'message' => 'Toko not found',
            ], 404);
        }

        DB::beginTransaction();
        try {
            // Create pesanan
            $pesanan = Pesanan::create([
                'toko_id' => $user->toko->id,
                'order_id' => Pesanan::generateOrderId(),
                'nama_lengkap' => $validated['nama_lengkap'],
                'whatsapp' => $validated['whatsapp'],
                'alamat' => $validated['alamat'],
                'catatan' => $validated['catatan'] ?? null,
                'total_amount' => 0, // Will be calculated
                'status' => Pesanan::STATUS_PENDING,
                'order_date' => now(),
            ]);

            // Create pesanan items
            $totalAmount = 0;
            foreach ($validated['items'] as $item) {
                $subtotal = $item['price'] * $item['quantity'];
                $totalAmount += $subtotal;

                PesananItem::create([
                    'pesanan_id' => $pesanan->id,
                    'produk_id' => $item['produk_id'],
                    'variant_id' => $item['variant_id'] ?? null,
                    'product_name' => $item['product_name'],
                    'price' => $item['price'],
                    'quantity' => $item['quantity'],
                    'subtotal' => $subtotal,
                ]);
            }

            // Update total amount
            $pesanan->update(['total_amount' => $totalAmount]);

            DB::commit();

            // Generate WhatsApp message
            $whatsappMessage = $this->generateWhatsAppMessage($pesanan, $user->toko);

            return response()->json([
                'success' => true,
                'message' => 'Pesanan created successfully',
                'pesanan' => $pesanan->load('items'),
                'whatsapp_message' => $whatsappMessage,
                'whatsapp_number' => $user->toko->nomor_telepon,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create pesanan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified pesanan
     */
    public function show($id)
    {
        $tokoId = Auth::user()->toko->id;
        $pesanan = Pesanan::where('toko_id', $tokoId)->with('items')->findOrFail($id);

        return response()->json([
            'success' => true,
            'pesanan' => $pesanan,
        ]);
    }

    /**
     * Update pesanan status
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,completed',
        ]);

        $tokoId = Auth::user()->toko->id;
        $pesanan = Pesanan::where('toko_id', $tokoId)->findOrFail($id);

        $pesanan->update([
            'status' => $request->status,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully',
            'pesanan' => $pesanan->fresh(),
        ]);
    }

    /**
     * Search pesanan
     */
    public function search(Request $request)
    {
        $tokoId = Auth::user()->toko->id;
        $search = $request->query('q');

        $pesanan = Pesanan::where('toko_id', $tokoId)
            ->search($search)
            ->with('items')
            ->get();

        return response()->json([
            'success' => true,
            'pesanan' => $pesanan,
        ]);
    }

    /**
     * Generate WhatsApp message
     */
    private function generateWhatsAppMessage($pesanan, $toko)
    {
        $message = "🛍️ *PESANAN BARU*\n\n";
        $message .= "📋 Order ID: {$pesanan->order_id}\n";
        $message .= "👤 Nama: {$pesanan->nama_lengkap}\n";
        $message .= "📍 Alamat: {$pesanan->alamat}\n\n";
        $message .= "🛒 *Detail Pesanan:*\n";

        foreach ($pesanan->items as $item) {
            $message .= "• {$item->product_name}\n";
            $message .= "  {$item->quantity}x @ Rp " . number_format($item->price, 0, ',', '.') . "\n";
            $message .= "  Subtotal: Rp " . number_format($item->subtotal, 0, ',', '.') . "\n\n";
        }

        $message .= "💰 *Total: Rp " . number_format($pesanan->total_amount, 0, ',', '.') . "*\n\n";

        if ($pesanan->catatan) {
            $message .= "📝 Catatan: {$pesanan->catatan}\n\n";
        }

        $message .= "Terima kasih telah memesan di {$toko->nama_toko}! 🙏";

        return urlencode($message);
    }
}