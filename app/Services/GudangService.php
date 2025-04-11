<?php

namespace App\Services;

use App\Repositories\GudangRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class GudangService
{
    protected $gudangRepository;

    public function __construct(GudangRepository $gudangRepository)
    {
        $this->gudangRepository = $gudangRepository;
    }

    public function getAll(int $perpage = 10)
    {
        return $this->gudangRepository->getAll($perpage);
    }

    public function getById($id)
    {
        return $this->gudangRepository->findById($id);
    }

    public function create(array $data)
    {

        $validator = Validator::make($data, [
            'name' => ['required', 'string', 'max:255', 'unique:gudangs,name'],
            'description' => ['nullable', 'string'],
        ],  [
            'name.required' => 'Nama gudang wajib diisi.',
            'name.unique' => 'Nama gudang sudah digunakan.',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }


        $validatedData = $validator->validated();
        $validatedData['slug'] = Str::slug($validatedData['name']);

        return DB::transaction(fn() => $this->gudangRepository->create($validatedData));
    }

    public function update(int $id, array $data)
    {
        $gudang = $this->gudangRepository->findById($id);

        if (!$gudang) {
            throw new \Exception('Gudang not found');
        }

        $validator = Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $validatedData = $validator->validated();
        $validatedData['slug'] = Str::slug($validatedData['name']);

        return DB::transaction(fn() => $this->gudangRepository->update($gudang, $validatedData));
    }

    public function delete(int $id)
    {
        $gudang = $this->gudangRepository->findById($id);
        if (!$gudang) {
            throw new \Exception('Gudang not found');
        }

        return DB::transaction(fn() => $this->gudangRepository->delete($gudang));
    }

    public function restore(int $id)
    {
        return $this->gudangRepository->restore($id);
    }

    public function forceDelete(int $id)
    {
        return $this->gudangRepository->forceDelete($id);
    }
}
