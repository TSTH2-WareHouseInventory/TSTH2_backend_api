<?php

namespace App\Services;

use App\Repositories\JenisBarangRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class JenisBarangService
{
    protected $jenisBarangRepository;

    public function __construct(JenisBarangRepository $jenisBarangRepository)
    {
        $this->jenisBarangRepository = $jenisBarangRepository;
    }

    public function getAll()
    {
        return $this->jenisBarangRepository->getAll();
    }

    public function getById($id)
    {
        return $this->jenisBarangRepository->getById($id);
    }

    public function create(array $data)
    {
        $validator = Validator::make($data, [
            'name' => ['required', 'string', 'max:255', 'unique:jenis_barangs,name'],
            'description' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $validatedData = $validator->validated();
        $validatedData['slug'] = Str::slug($validatedData['name']);
        $validatedData['user_id'] = auth()->id();

        return DB::transaction(fn() => $this->jenisBarangRepository->create($validatedData));
    }

    public function update($id, array $data)
    {
        $jenisBarang = $this->jenisBarangRepository->getById($id);
        if (!$jenisBarang) {
            throw new \Exception('Jenis barang tidak ditemukan');
        }

        $validator = Validator::make($data, [
            'name' => ['required', 'string', 'max:255', "unique:jenis_barangs,name,$id,id"],
            'description' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $validatedData = $validator->validated();
        $validatedData['slug'] = Str::slug($validatedData['name']);
        $validatedData['user_id'] = auth()->id();

        return DB::transaction(function () use ($jenisBarang, $validatedData) {
            $this->jenisBarangRepository->update($jenisBarang, $validatedData);
            return $jenisBarang->fresh();
        });
    }

    public function delete($id)
    {
        return DB::transaction(function () use ($id) {
            $jenisBarang = $this->jenisBarangRepository->getById($id);
            if (!$jenisBarang) {
                // throw new \Exception('Jenis barang tidak ditemukan');\
                return null;
            }
            $this->jenisBarangRepository->delete($jenisBarang);
            return $jenisBarang;
        });
    }

    public function restore($id)
    {
        return DB::transaction(function () use ($id) {
            $jenisBarang = $this->jenisBarangRepository->getTrashedById($id);
            if (!$jenisBarang) {
                throw new \Exception('Jenis barang tidak ditemukan atau belum dihapus');
            }
            $this->jenisBarangRepository->restore($jenisBarang);
            return $jenisBarang->fresh();
        });
    }

    public function forceDelete($id)
    {
        return DB::transaction(function () use ($id) {
            $jenisBarang = $this->jenisBarangRepository->getTrashedById($id);
            if (!$jenisBarang) {
                throw new \Exception('Jenis barang tidak ditemukan atau belum dihapus');
            }
            $this->jenisBarangRepository->forceDelete($jenisBarang);
            return $jenisBarang;
        });
    }
}
