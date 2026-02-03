<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePesananRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'url_toko' => 'required|string|exists:users,url_toko',
            'nama_lengkap' => 'required|string|max:255',
            'whatsapp' => 'required|string|max:20',
            'alamat' => 'required|string',
            'catatan' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.produk_id' => 'required|exists:produk,id',
            'items.*.variant_id' => 'nullable|exists:produk_variant,id',
            'items.*.product_name' => 'required|string|max:255',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.quantity' => 'required|integer|min:1',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'url_toko.required' => 'URL toko harus diisi',
            'url_toko.exists' => 'Toko tidak ditemukan',
            'nama_lengkap.required' => 'Nama lengkap harus diisi',
            'whatsapp.required' => 'Nomor WhatsApp harus diisi',
            'alamat.required' => 'Alamat harus diisi',
            'items.required' => 'Keranjang tidak boleh kosong',
            'items.min' => 'Minimal harus ada 1 item',
            'items.*.produk_id.required' => 'Produk harus dipilih',
            'items.*.produk_id.exists' => 'Produk tidak ditemukan',
            'items.*.product_name.required' => 'Nama produk harus diisi',
            'items.*.price.required' => 'Harga harus diisi',
            'items.*.quantity.required' => 'Jumlah harus diisi',
            'items.*.quantity.min' => 'Jumlah minimal 1',
        ];
    }
}