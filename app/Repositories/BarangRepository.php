<?php

namespace App\Repositories;

use App\Models\Barang;

class BarangRepository
{
    public function getAll()
    {
        return Barang::with(['gudangs' => function ($query) {
            $query->withPivot('stok_tersedia', 'stok_dipinjam', 'stok_maintenance');
        }])->get();
    }

    public function findById($id)
    {
        return Barang::with(['gudangs' => function ($query) {
            $query->withPivot('stok_tersedia', 'stok_dipinjam', 'stok_maintenance');
        }])->find($id);
    }


    public function create(array $data)
    {
        return Barang::create($data);
    }

    public function update(Barang $barang, array $data)
    {
        return $barang->update($data);
    }

    public function delete(Barang $barang)
    {
        return $barang->delete();
    }
}
