<?php

namespace App\Services;

use App\Repositories\RoleRepository;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class RoleService
{
    protected $roleRepository;

    public function __construct(RoleRepository $roleRepository)
    {
        $this->roleRepository = $roleRepository;
    }

    public function getAll()
    {
        return $this->roleRepository->getAll();
    }

    public function getById($id)
    {
        return $this->roleRepository->findById($id);
    }

    public function create(array $data)
    {
        $validator = Validator::make($data, [
            'name' => 'required|string|unique:roles,name',
            'guard_name' => 'web'
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $this->roleRepository->create([
            'name' => $data['name']
        ]);
    }

    public function update($id, array $data)
    {
        $role = $this->roleRepository->findById($id);
        if (!$role) return null;

        $validator = Validator::make($data, [
            'name' => 'required|string|unique:roles,name,' . $id,
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $this->roleRepository->update($role, ['name' => $data['name']]);
        return $role;
    }

    public function delete($id)
    {
        $role = $this->roleRepository->findById($id);
        if (!$role) return false;

        return $this->roleRepository->delete($role);
    }
}
