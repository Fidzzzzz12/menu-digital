<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pesanan extends Model
{
    use HasFactory;

    protected $table = 'pesanan';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'url_toko',
        'order_id',
        'nama_lengkap',
        'whatsapp',
        'alamat',
        'catatan',
        'total_harga',
        'status',
        'order_date',
        // Shipping fields
        'metode_pengiriman',
        'provinsi_id',
        'provinsi_nama',
        'kota_id',
        'kota_nama',
        'kurir',
        'layanan_kurir',
        'layanan_pengiriman',
        'estimasi_kirim',
        'ongkir',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'total_harga' => 'decimal:2',
        'order_date' => 'datetime',
    ];

    /**
     * Status constants
     */
    const STATUS_PENDING = 'pending';
    const STATUS_DIKONFIRMASI = 'dikonfirmasi';
    const STATUS_SELESAI = 'selesai';
    const STATUS_DIBATALKAN = 'dibatalkan';

    /**
     * Get the user that owns the pesanan.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the items for the pesanan.
     */
    public function items()
    {
        return $this->hasMany(PesananItem::class);
    }

    /**
     * Scope a query to only include pending orders.
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope a query to only include confirmed orders.
     */
    public function scopeDikonfirmasi($query)
    {
        return $query->where('status', self::STATUS_DIKONFIRMASI);
    }

    /**
     * Scope a query to only include completed orders.
     */
    public function scopeSelesai($query)
    {
        return $query->where('status', self::STATUS_SELESAI);
    }

    /**
     * Scope a query to only include cancelled orders.
     */
    public function scopeDibatalkan($query)
    {
        return $query->where('status', self::STATUS_DIBATALKAN);
    }

    /**
     * Scope a query to search by order ID or customer name.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('order_id', 'like', '%' . $search . '%')
              ->orWhere('nama_lengkap', 'like', '%' . $search . '%');
        });
    }

    /**
     * Generate unique order ID.
     */
    public static function generateOrderId()
    {
        return 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
    }

    /**
     * Calculate total amount from items.
     */
    public function calculateTotal()
    {
        $this->total_harga = $this->items()->sum('subtotal');
        $this->save();
    }
}