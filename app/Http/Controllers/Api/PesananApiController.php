<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pesanan;
use App\Models\PesananItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PesananApiController extends Controller
{
    public function store(Request $request)
    {
        try {
            // Validasi
            $validated = $request->validate([
                'url_toko' => 'required|string',
                'nama_lengkap' => 'required|string',
                'whatsapp' => 'required|string',
                'alamat' => 'required|string',
                'catatan' => 'nullable|string',
                'metode_pengiriman' => 'required|in:dikirim,ambil_sendiri',
                'ongkir' => 'nullable|integer',
                'kurir' => 'nullable|string',
                'layanan_kurir' => 'nullable|string',
                'estimasi_kirim' => 'nullable|string',
                'items' => 'required|array',
                'items.*.produk_id' => 'required|integer',
                'items.*.nama_produk' => 'required|string',
                'items.*.variant' => 'nullable|string',
                'items.*.harga' => 'required|numeric',
                'items.*.quantity' => 'required|integer|min:1'
            ]);

            // Find user by url_toko
            $toko = \App\Models\Toko::where('url_toko', $validated['url_toko'])->first();
            if (!$toko) {
                return response()->json([
                    'success' => false,
                    'message' => 'Toko tidak ditemukan'
                ], 404);
            }

            DB::beginTransaction();

            // Hitung total
            $subtotal = 0;
            foreach ($validated['items'] as $item) {
                $subtotal += $item['harga'] * $item['quantity'];
            }

            $ongkir = $validated['ongkir'] ?? 0;
            $totalHarga = $subtotal + $ongkir;

            // Simpan pesanan
            $pesanan = Pesanan::create([
                'user_id' => $toko->user_id,
                'url_toko' => $validated['url_toko'],
                'order_id' => Pesanan::generateOrderId(),
                'nama_lengkap' => $validated['nama_lengkap'],
                'whatsapp' => $validated['whatsapp'],
                'alamat' => $validated['alamat'],
                'catatan' => $validated['catatan'] ?? '',
                'metode_pengiriman' => $validated['metode_pengiriman'],
                'ongkir' => $ongkir,
                'kurir' => $validated['kurir'] ?? null,
                'layanan_kurir' => $validated['layanan_kurir'] ?? null,
                'estimasi_kirim' => $validated['estimasi_kirim'] ?? null,
                'total_harga' => $totalHarga,
                'status' => 'pending',
                'order_date' => now()
            ]);

            // Simpan detail pesanan
            foreach ($validated['items'] as $item) {
                PesananItem::create([
                    'pesanan_id' => $pesanan->id,
                    'produk_id' => $item['produk_id'],
                    'nama_produk' => $item['nama_produk'],
                    'variant' => $item['variant'] ?? null,
                    'harga' => $item['harga'],
                    'quantity' => $item['quantity'],
                    'subtotal' => $item['harga'] * $item['quantity']
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pesanan berhasil disimpan',
                'data' => [
                    'pesanan_id' => $pesanan->id,
                    'total_harga' => $totalHarga,
                    'ongkir' => $ongkir,
                    'subtotal' => $subtotal
                ]
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}