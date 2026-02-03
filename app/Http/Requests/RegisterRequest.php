<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|max:255',
            'nama_toko' => 'required|string|max:255',
            'url_toko' => 'required|string|max:100|unique:users,url_toko|regex:/^[a-zA-Z0-9-]+$/',
            'nomor_telepon' => 'required|string|max:20',
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
            'email.required' => 'Email harus diisi',
            'email.email' => 'Format email tidak valid',
            'email.unique' => 'Email sudah terdaftar',
            'password.required' => 'Password harus diisi',
            'password.min' => 'Password minimal 8 karakter',
            'nama_toko.required' => 'Nama toko harus diisi',
            'url_toko.required' => 'URL toko harus diisi',
            'url_toko.unique' => 'URL toko sudah digunakan',
            'url_toko.regex' => 'URL toko hanya boleh berisi huruf, angka, dan tanda hubung',
            'nomor_telepon.required' => 'Nomor telepon harus diisi',
        ];
    }
}