<?php

namespace App\Models;

use Database\Seeders\JenisBarangSeeder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Barang extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'barangs';
    protected $fillable = [
        'jenisbarang_id',
        'satuan_id',
        'barangcategory_id',
        'barang_kode',
        'barang_nama',
        'barang_slug',
        'barang_harga',
        'barang_gambar',
        'user_id'

    ];



    public function jenisBarang()
    {
        return $this->belongsTo(JenisBarang::class, 'jenisbarang_id');
    }
    public function category()
    {
        return $this->belongsTo(BarangCategory::class, 'barangcategory_id');
    }

    // Relasi ke Satuan
    public function satuan()
    {
        return $this->belongsTo(Satuan::class, 'satuan_id');
    }

    public function gudangs()
    {
        return $this->belongsToMany(Gudang::class, 'barang_gudangs')->withPivot('stok_tersedia', 'stok_dipinjam', 'stok_maintenance');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($barang) {
            if (empty($barang->barang_kode)) {
                $barang->barang_kode = 'BRG-' . rand(100000, 999999);
            }
        });
    }

    protected  $casts = [
        'created_at' => 'datetime:Y-m-d H:m:s',
        'updated_at' => 'datetime:Y-m-d H:m:s',
        'deleted_at' => 'datetime:Y-m-d H:m:s',
    ];
}
