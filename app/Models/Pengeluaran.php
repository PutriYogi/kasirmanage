<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengeluaran extends Model
{
    use HasFactory;

    protected $fillable = [
        'jenis_pengeluaran',
        'nominal',
        'keterangan',
        'tanggal'
    ];

    protected $casts = [
        'tanggal' => 'date',
        'nominal' => 'decimal:2'
    ];

    // Scope untuk filter berdasarkan tanggal
    public function scopeByDate($query, $date)
    {
        return $query->whereDate('tanggal', $date);
    }

    // Scope untuk filter berdasarkan bulan
    public function scopeByMonth($query, $month, $year = null)
    {
        $year = $year ?? date('Y');
        return $query->whereMonth('tanggal', $month)->whereYear('tanggal', $year);
    }

    // Scope untuk filter berdasarkan jenis pengeluaran
    public function scopeByJenis($query, $jenis)
    {
        return $query->where('jenis_pengeluaran', $jenis);
    }
}