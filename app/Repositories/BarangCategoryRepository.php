<?php

namespace App\Repositories;

use App\Models\BarangCategory;

class BarangCategoryRepository
{
    public function getAll()
    {
        return BarangCategory::all();
    }

    public function findById($id)
    {
        return BarangCategory::find($id);
    }

    public function create(array $data)
    {
        return BarangCategory::create($data);
    }

    public function update(BarangCategory $barangCategory, array $data)
    {
        return $barangCategory->update($data);
    }

    public function delete(BarangCategory $barangCategory)
    {
        return $barangCategory->delete();
    }
}
