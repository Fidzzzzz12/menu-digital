<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kategori extends Model
{
    use HasFactory;

    protected $table = 'kategori';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'nama_kategori',
        'deskripsi',
    ];

    /**
     * Get the user that owns the kategori.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the produk for the kategori.
     */
    public function produk()
    {
        return $this->hasMany(Produk::class, 'kategori_id');
    }
}