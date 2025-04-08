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
        return Satuan::find($id);
    }

    public function findTrashedById($id)
    {
        return Satuan::onlyTrashed()->find($id);
    }

    public function create(array $data)
    {
        return Satuan::create($data);
    }

    public function update(Satuan $satuan, array $data)
    {
        return $satuan->update($data);
    }

    public function delete(Satuan $satuan)
    {
        return $satuan->delete();
    }

    public function restore(Satuan $satuan)
    {
        return $satuan->restore();
    }

    public function forceDelete(Satuan $satuan)
    {
        return $satuan->forceDelete();
    }
}
