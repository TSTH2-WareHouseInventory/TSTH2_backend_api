<?php

namespace App\Repositories;

use App\Models\Satuan;
use Illuminate\Database\Eloquent\Collection;

class SatuanRepository
{
    public function getAll()
    {
        return Satuan::with('user')->get();
    }

    public function findById($id)
    {
        return Satuan::with('user')->find($id);
    }

    public function findTrashedByName($name)
    {
        return Satuan::onlyTrashed()->where('name', $name)->first();
    }

    public function create(array $data)
    {
        return Satuan::create($data);
    }

    public function update($id, array $data)
    {
        $satuan = $this->findById($id);

        if (!$satuan) {
            throw new \Exception('Satuan barang tidak ditemukan');
        }

        $satuan->update($data);
        return $satuan;
    }


    public function delete(Satuan $satuan)
    {
        return $satuan->delete();
    }

    public function restore(int $id): ?Satuan
    {
        $satuan = Satuan::onlyTrashed()->find($id);
        if ($satuan) {
            $satuan->restore();
            return $satuan;
        }
        return null;
    }

    public function forceDelete(Satuan $satuan)
    {
        return $satuan->forceDelete();
    }
}
