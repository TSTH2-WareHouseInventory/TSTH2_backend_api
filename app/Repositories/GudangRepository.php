<?php

namespace App\Repositories;

use App\Models\Gudang;
use App\Models\User;

class GudangRepository
{
    protected $model;

    public function __construct(Gudang $gudang)
    {
        $this->model = $gudang;
    }
    public function getAll()
    {
        return Gudang::get();
    }

    public function findById(int $id): ?Gudang
    {
        return Gudang::find($id);
    }

    public function create(array $data): Gudang
    {
        return Gudang::create($data);
    }

    public function findByUserId($userId)
    {
        return $this->model->where('user_id', $userId)->first();
    }

    public function findTrashedByName($name)
    {
        return Gudang::onlyTrashed()->where('name', $name)->first();
    }

    public function update(Gudang $gudang, array $data): bool
    {
        return $gudang->update($data);
    }

    public function delete(Gudang $gudang): bool
    {
        return $gudang->delete();
    }

    public function restore(int $id): ?Gudang
    {
        $gudang = Gudang::onlyTrashed()->find($id);
        if ($gudang) {
            $gudang->restore();
            return $gudang;
        }
        return null;
    }

    public function forceDelete(int $id): bool
    {
        $gudang = Gudang::onlyTrashed()->find($id);
        return $gudang ? $gudang->forceDelete() : false;
    }
}
