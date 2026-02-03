<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProdukVariant extends Model
{
    use HasFactory;

    protected $table = 'produk_variant';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'produk_id',
        'nama_variant',
        'harga_tambahan',
        'gambar_variant',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'harga_tambahan' => 'decimal:2',
    ];

    /**
     * Get the produk that owns the variant.
     */
    public function produk()
    {
        return $this->belongsTo(Produk::class);
    }

    /**
     * Get the pesanan items for the variant.
     */
    public function pesananItems()
    {
        return $this->hasMany(PesananItem::class, 'variant_id');
    }
}