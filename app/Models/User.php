<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'password',
        'nama_toko',
        'url_toko',
        'nomor_telepon',
        'registered_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'registered_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Get the toko associated with the user.
     */
    public function toko()
    {
        return $this->hasOne(Toko::class);
    }

    /**
     * Get the kategori for the user.
     */
    public function kategori()
    {
        return $this->hasMany(Kategori::class);
    }

    /**
     * Get the produk for the user.
     */
    public function produk()
    {
        return $this->hasMany(Produk::class);
    }

    /**
     * Get the pesanan for the user.
     */
    public function pesanan()
    {
        return $this->hasMany(Pesanan::class);
    }

    /**
     * Get the dashboard stats for the user.
     */
    public function dashboardStats()
    {
        return $this->hasMany(DashboardStat::class);
    }

    /**
     * Get the statistik harian for the user.
     */
    public function statistikHarian()
    {
        return $this->hasMany(StatistikHarian::class);
    }
}