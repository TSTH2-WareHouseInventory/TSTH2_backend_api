<?php

namespace App\Services;

use App\Models\Satuan;
use Illuminate\Support\Str;

class SatuanService
{
    public function getAll()
    {
        return Satuan::with('user')->paginate(10);
    }

    public function getById($id)
    {
        return Satuan::with('user')->find($id);
    }

    public function findTrashedByName($name)
    {
        return Satuan::onlyTrashed()->where('name', $name)->first();
    }

    public function create(array $data)
    {
        $validatedData = $this->validateData($data);
        $validatedData['slug'] = Str::slug($validatedData['name']);
        $validatedData['user_id'] = auth()->id();

        return Satuan::create($validatedData);
    }

    public function update($id, array $data)
    {
        $satuan = Satuan::find($id);
        if (!$satuan) {
            throw new \Exception('Satuan barang tidak ditemukan');
        }

        $validatedData = $this->validateData($data, $id);
        $validatedData['slug'] = Str::slug($validatedData['name']);
        $validatedData['user_id'] = auth()->id();

        $satuan->update($validatedData);
        return $satuan;
    }

    public function delete($id)
    {
        $satuan = Satuan::find($id);
        if ($satuan) {
            $satuan->delete();
        }
    }

    public function restore($id)
    {
        $satuan = Satuan::onlyTrashed()->find($id);
        if ($satuan) {
            $satuan->restore();
        }
        return $satuan;
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
