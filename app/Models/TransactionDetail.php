<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransactionDetail extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'transaction_id',
        'barang_status_id',
        'barang_id',
        'gudang_id',
        'quantity',
        'tanggal_kembali'
    ];
    public function statusBarang()
    {
        return $this->belongsTo(BarangStatus::class);
    }


    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }
    public function gudang()
    {
        return $this->belongsTo(Gudang::class);
    }
}
