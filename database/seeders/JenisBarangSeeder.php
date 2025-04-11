<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JenisBarangSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('jenis_barangs')->insert([
            ['name' => 'Perkakas', 'slug' => 'perkakas', 'description' => null, 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Fungisida', 'slug' => 'fungisida', 'description' => null, 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Insektisida', 'slug' => 'insektisida', 'description' => null, 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Herbisida', 'slug' => 'herbisida', 'description' => null, 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Bakterisida', 'slug' => 'bakterisida', 'description' => null, 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Rodentisida', 'slug' => 'rodentisida', 'description' => null, 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Moluskisida', 'slug' => 'moluskisida', 'description' => null, 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Nematisida', 'slug' => 'nematisida', 'description' => null, 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Pupuk', 'slug' => 'pupuk', 'description' => null, 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Bahan Bakar', 'slug' => 'bahan-bakar', 'description' => null, 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Keperluan Lapangan', 'slug' => 'keperluan-lapangan', 'description' => null, 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Perekat & Perata', 'slug' => 'perekat-perata', 'description' => null, 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Bakterisida & Fungisida', 'slug' => 'bakterisida-fungisida', 'description' => null, 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Zat Pengatur Tumbuh', 'slug' => 'zat-pengatur-tumbuh', 'description' => null, 'user_id' => 1, 'created_at' => now(), 'updated_at' => now()]
        ]);
    }
}
