<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Satuan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'satuans';
    protected $primaryKey = 'id';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'user_id',
    ];

    protected  $casts = [
        'created_at' => 'datetime:Y-m-d H:m:s',
        'updated_at' => 'datetime:Y-m-d H:m:s',
        'deleted_at' => 'datetime:Y-m-d H:m:s',
    ];

    public function barangs()
    {
        return $this->hasMany(Barang::class, 'satuan_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
