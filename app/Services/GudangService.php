<?php

namespace App\Services;

use App\Models\Gudang;
use App\Repositories\GudangRepository;
use App\Rules\IsOperatorUser;
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

    public function getAll()
    {
        return $this->gudangRepository->getAll();
    }

    public function getById(int $id)
    {
        return $this->gudangRepository->findById($id);
    }

    public function findTrashedByName($name)
    {
        return $this->gudangRepository->findTrashedByName($name);
    }

    public function create(array $data)
    {
        // Validasi input, termasuk rule khusus IsOperatorUser
        $validator = Validator::make($data, [
            'name'        => ['required', 'string', 'max:255', 'unique:gudangs,name'],
            'description' => ['nullable', 'string'],
            'user_id'     => ['required', 'exists:users,id', new IsOperatorUser()],
        ], [
            'name.required'     => 'Nama gudang wajib diisi.',
            'name.unique'       => 'Nama gudang sudah digunakan.',
            'user_id.required'  => 'User operator wajib dipilih.',
            'user_id.exists'    => 'User tidak ditemukan.',

        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $validated = $validator->validated();
        $validated['slug'] = Str::slug($validated['name']);

        // ⛔ Cek user_id unik di gudang lain
        $existingGudang = $this->gudangRepository->findByUserId($validated['user_id']);
        if ($existingGudang) {
            throw ValidationException::withMessages([
                'user_id' => 'User ini sudah diassign ke gudang lain.',
            ]);
        }
        // Simpan dalam transaction
        return DB::transaction(fn() => $this->gudangRepository->create($validated));
    }

    // public function update(int $id, array $data)
    // {
    //     $gudang = $this->gudangRepository->findById($id);

    //     if (!$gudang) {
    //         throw new \Exception('Gudang not found');
    //     }

    //     $validator = Validator::make($data, [
    //         'name' => ['required', 'string', 'max:255'],
    //         'description' => ['nullable', 'string'],
    //         'user_id'     => ['required', 'exists:users,id', new IsOperatorUser()],
    //     ]);

    //     if ($validator->fails()) {
    //         throw new ValidationException($validator);
    //     }

    //     $validatedData = $validator->validated();
    //     $validatedData['slug'] = Str::slug($validatedData['name']);

    //     // ⛔ Cek user_id unik di gudang lain (kecuali dirinya sendiri)
    //     $existingGudang = $this->gudangRepository->findByUserId($validatedData['user_id']);
    //     if ($existingGudang && $existingGudang->id !== $gudang->id) {
    //         throw ValidationException::withMessages([
    //             'user_id' => 'User ini sudah diassign ke gudang lain.',
    //         ]);
    //     }
    //     return DB::transaction(fn() => $this->gudangRepository->update($gudang, $validatedData));
    // }

    public function update(int $id, array $data)
    {
        // Cari gudang yang akan diupdate
        $gudang = $this->gudangRepository->findById($id);

        if (!$gudang) {
            throw new \Exception('Gudang not found');
        }

        // Validasi data yang masuk
        $validator = Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'user_id'     => ['required', 'exists:users,id', new IsOperatorUser()],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $validatedData = $validator->validated();
        $validatedData['slug'] = Str::slug($validatedData['name']);

        // Pengecekan apakah slug sudah ada, kecuali untuk gudang yang sedang diupdate
        $existingSlug = Gudang::where('slug', $validatedData['slug'])
            ->where('id', '!=', $gudang->id)
            ->first();

        if ($existingSlug) {
            throw ValidationException::withMessages([
                'slug' => 'Slug untuk nama gudang ini sudah digunakan.',
            ]);
        }

        // Pengecekan user_id unik di gudang lain (kecuali dirinya sendiri)
        $existingGudang = $this->gudangRepository->findByUserId($validatedData['user_id']);
        if ($existingGudang && $existingGudang->id !== $gudang->id) {
            throw ValidationException::withMessages([
                'user_id' => 'User ini sudah diassign ke gudang lain.',
            ]);
        }

        // Lakukan update di repository
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
