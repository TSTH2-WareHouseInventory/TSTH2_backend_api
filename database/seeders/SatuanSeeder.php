<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SatuanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('satuans')->insert([
            ['name' => 'Gulung', 'slug' => 'gulung', 'description' => null, 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Unit', 'slug' => 'unit', 'description' => null, 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Kg', 'slug' => 'kg', 'description' => null, 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Gram', 'slug' => 'gram', 'description' => null, 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Mililiter', 'slug' => 'mililiter', 'description' => null, 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Liter', 'slug' => 'liter', 'description' => null, 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()]
        ]);
    }
}
