<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
   protected $fillable = [
    'name',
    'guard_name'
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }
    public $attributes = [
        'guard_name' => 'api',
    ];

}
