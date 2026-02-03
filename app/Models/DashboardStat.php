<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DashboardStat extends Model
{
    use HasFactory;

    protected $table = 'dashboard_stats';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'total_pendapatan',
        'jumlah_transaksi',
        'jumlah_menu',
        'jumlah_kategori',
        'bulan',
        'tahun',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'total_pendapatan' => 'decimal:2',
        'jumlah_transaksi' => 'integer',
        'jumlah_menu' => 'integer',
        'jumlah_kategori' => 'integer',
        'bulan' => 'integer',
        'tahun' => 'integer',
    ];

    /**
     * Get the user that owns the stats.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to filter by month and year.
     */
    public function scopeByPeriod($query, $month, $year)
    {
        return $query->where('bulan', $month)->where('tahun', $year);
    }

    /**
     * Scope a query to filter by year.
     */
    public function scopeByYear($query, $year)
    {
        return $query->where('tahun', $year);
    }
}