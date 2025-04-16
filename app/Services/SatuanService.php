<?php

namespace App\Services;

use App\Models\Satuan;
use App\Repositories\SatuanRepository;
use Illuminate\Support\Str;

class SatuanService
{

    protected $satuanRepository;

    public function __construct(SatuanRepository $satuanRepository)
    {
        $this->satuanRepository = $satuanRepository;
    }

    public function getAll()
    {
        return $this->satuanRepository->getAll();
    }

    public function getById($id)
    {
        return $this->satuanRepository->findById($id);
    }

    public function findTrashedByName($name)
    {
        return $this->satuanRepository->findTrashedByName($name);
    }

    public function create(array $data)
    {
        $validatedData = $this->validateData($data);
        $validatedData['slug'] = Str::slug($validatedData['name']);
        $validatedData['user_id'] = auth()->id();

        return $this->satuanRepository->create($validatedData);
    }

    public function update($id, array $data)
    {
        // Panggil update di repository dan masukkan id dan data
        $validatedData = $this->validateData($data, $id);
        $validatedData['slug'] = Str::slug($validatedData['name']);
        $validatedData['user_id'] = auth()->id();



        
        return $this->satuanRepository->update($id, $validatedData);
    }

    public function delete($id)
    {
        $satuan = $this->satuanRepository->findById($id);

        if (!$satuan) {
            throw new \Exception('Satuan not found');
        }
        return $this->satuanRepository->delete($satuan);
    }

    public function restore($id)
    {
        return $this->satuanRepository->restore($id);
    }

    private function validateData(array $data, $id = null)
    {
        $rules = [
            'name' => ['required', 'string', 'max:255', "unique:satuans,name,$id,id"],
            'description' => ['nullable', 'string'],
            'user_id' => ['nullable', 'exists:users,id'],
        ];

        return validator($data, $rules)->validate();
    }
}
