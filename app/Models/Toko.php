<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Toko extends Model
{
    use HasFactory;

    protected $table = 'toko';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'nama_lengkap',
        'nama_toko',
        'url_toko',
        'email',
        'nomor_telepon',
        'alamat',
        'deskripsi',
        'foto_profil',
        'banner_toko',
        'banner_position',
        'banner_zoom',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'banner_position' => 'array',
        'banner_zoom' => 'integer',
    ];

    /**
     * Get the user that owns the toko.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the kategori for the toko.
     */
    public function kategori()
    {
        return $this->hasMany(Kategori::class, 'user_id', 'user_id');
    }

    /**
     * Get the produk for the toko.
     */
    public function produk()
    {
        return $this->hasMany(Produk::class, 'user_id', 'user_id');
    }

    /**
     * Get the pesanan for the toko.
     */
    public function pesanan()
    {
        return $this->hasMany(Pesanan::class, 'user_id', 'user_id');
    }

    /**
     * Get the dashboard stats for the toko.
     */
    public function dashboardStats()
    {
        return $this->hasMany(DashboardStat::class, 'user_id', 'user_id');
    }

    /**
     * Get the daily statistics for the toko.
     */
    public function statistikHarian()
    {
        return $this->hasMany(StatistikHarian::class, 'user_id', 'user_id');
    }
}