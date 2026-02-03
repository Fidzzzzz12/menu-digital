<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StatistikHarian extends Model
{
    use HasFactory;

    protected $table = 'statistik_harian';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'tanggal',
        'total_pendapatan',
        'jumlah_transaksi',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'tanggal' => 'date',
        'total_pendapatan' => 'decimal:2',
        'jumlah_transaksi' => 'integer',
    ];

    /**
     * Get the user that owns the stats.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to filter by date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('tanggal', [$startDate, $endDate]);
    }

    /**
     * Scope a query to filter by specific date.
     */
    public function scopeByDate($query, $date)
    {
        return $query->whereDate('tanggal', $date);
    }
}