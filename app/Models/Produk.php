<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produk extends Model
{
    use HasFactory;

    protected $table = 'produk';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'kategori_id',
        'nama_produk',
        'harga',
        'stok',
        'deskripsi',
        'gambar',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'harga' => 'decimal:2',
        'stok' => 'integer',
    ];

    /**
     * Get the user that owns the produk.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the kategori that owns the produk.
     */
    public function kategori()
    {
        return $this->belongsTo(Kategori::class);
    }

    /**
     * Get the variants for the produk.
     */
    public function variants()
    {
        return $this->hasMany(ProdukVariant::class, 'produk_id');
    }

    /**
     * Get the pesanan items for the produk.
     */
    public function pesananItems()
    {
        return $this->hasMany(PesananItem::class, 'produk_id');
    }

    /**
     * Scope a query to only include products with stock.
     */
    public function scopeInStock($query)
    {
        return $query->where('stok', '>', 0);
    }

    /**
     * Scope a query to search products by name.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where('nama_produk', 'like', '%' . $search . '%');
    }

    /**
     * Scope a query to filter by category.
     */
    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('kategori_id', $categoryId);
    }
}