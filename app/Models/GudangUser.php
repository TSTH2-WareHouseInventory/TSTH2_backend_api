<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GudangUser extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['gudang_id', 'user_id'];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:m:s',
        'updated_at' => 'datetime:Y-m-d H:m:s',
        'deleted_at' => 'datetime:Y-m-d H:m:s',
    ];

    public function gudang()
    {
        return $this->belongsTo(Gudang::class, 'gudang_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
