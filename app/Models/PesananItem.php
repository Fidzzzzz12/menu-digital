<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PesananItem extends Model
{
    use HasFactory;

    protected $table = 'pesanan_item';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'pesanan_id',
        'produk_id',
        'nama_produk',
        'variant',
        'harga',
        'quantity',
        'subtotal',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'harga' => 'decimal:2',
        'quantity' => 'integer',
        'subtotal' => 'decimal:2',
    ];

    /**
     * Get the pesanan that owns the item.
     */
    public function pesanan()
    {
        return $this->belongsTo(Pesanan::class);
    }

    /**
     * Get the produk associated with the item.
     */
    public function produk()
    {
        return $this->belongsTo(Produk::class);
    }

    /**
     * Calculate subtotal.
     */
    public function calculateSubtotal()
    {
        $this->subtotal = $this->harga * $this->quantity;
        return $this->subtotal;
    }
}