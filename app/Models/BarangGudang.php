<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BarangGudang extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'barang_id',
        'gudang_id',
        'stok_tersedia',
        'stok_dipinjam',
        'stok_maintenance',
    ];

    public function barang(){
        return $this->belongsTo(Barang::class );
    }

    public function gudang(){
        return $this->belongsTo(Gudang::class );
    }
}
