<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 
        'total', 
        'dibayarkan', 
        'kembalian', 
        'kasir_name', 
        'status',
        'kode_transaksi'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Generate kode transaksi jika belum ada
            if (empty($model->kode_transaksi)) {
                $model->kode_transaksi = 'TRX-' . date('Ymd') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
            }
        });
    }

    // Relationship dengan detail transaksi
    public function details()
    {
        return $this->hasMany(TransaksiDetail::class, 'transaksi_id');
    }

    // Method untuk menghitung total berdasarkan detail
    public function calculateTotal()
    {
        return $this->details()->sum('subtotal');
    }

    // Method untuk update total berdasarkan detail
    public function updateTotalFromDetails()
    {
        $total = $this->calculateTotal();
        $this->update(['total' => $total]);
        return $total;
    }

    // Accessor untuk format rupiah
    public function getTotalFormattedAttribute()
    {
        return 'Rp ' . number_format($this->total, 0, ',', '.');
    }
}
