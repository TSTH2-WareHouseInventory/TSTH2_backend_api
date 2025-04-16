<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class UserService
{
    protected $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function getAll()
    {
        return $this->userRepository->getAll();
    }

    public function getOperators()
    {
        return $this->userRepository->getOperators();
    }


    public function getById($id)
    {
        return $this->userRepository->getById($id);
    }

    public function create(array $data)
    {
        if (User::where('name', $data['name'])->exists()) {
            throw new \Exception('Nama sudah digunakan.');
        }

        if (User::where('email', $data['email'])->exists()) {
            throw new \Exception('Email sudah digunakan.');
        }

        $validator = Validator::make($data, [
            'name' => 'required|string|max:255|unique:users,name',
            'email' => 'required|email|unique:users,email',
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*[\W_]).+$/',
                'confirmed'
            ],
            'roles' => 'required|array',
            'roles.*' => 'string|exists:roles,name',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $data['password'] = Hash::make($data['password']);

        $user = $this->userRepository->create($data);
        $user->syncRoles($data['roles']);

        return $user;
    }

    public function update($id, array $data)
    {
        $user = $this->userRepository->getById($id);
        if (!$user) {
            throw new \Exception('User tidak ditemukan');
        }

        if (isset($data['name']) && User::where('name', $data['name'])->where('id', '!=', $id)->exists()) {
            throw new \Exception('Nama sudah digunakan.');
        }

        if (isset($data['email']) && User::where('email', $data['email'])->where('id', '!=', $id)->exists()) {
            throw new \Exception('Email sudah digunakan.');
        }

        $validator = Validator::make($data, [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $id,
            'phone_number' => 'nullable|string|max:15|unique:users,phone_number,' . $id,
            'avatar' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        if (array_key_exists('avatar', $data) && $data['avatar'] !== null && $data['avatar'] !== '') {
            if ($user->avatar && $user->avatar !== 'default_avatar.png') {
                Storage::disk('public')->delete($user->avatar);
            }
            $avatarPath = uploadBase64Image($data['avatar'], 'img/profil');
            $data['avatar'] = $avatarPath;
        } else {
            unset($data['avatar']);
        }

        $this->userRepository->update($user, $data);

        return $this->userRepository->getById($id);
    }

    public function deleteAvatar($id)
    {
        $user = $this->userRepository->getById($id);
        if (!$user) {
            throw new \Exception('User tidak ditemukan');
        }

        if ($user->avatar && $user->avatar !== 'default_avatar.png') {
            Storage::disk('public')->delete($user->avatar);
        }

        // Set avatar jadi default
        $user->avatar = 'default_avatar.png';
        $user->save();

        return $user;
    }


    public function changePassword($id, array $data)
    {
        $user = $this->userRepository->getById($id);
        if (!$user) {
            throw new \Exception('User tidak ditemukan');
        }

        $validator = Validator::make($data, [
            'current_password' => 'required',
            'new_password' => [
                'required',
                'string',
                'min:8',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*[\W_]).+$/',
                'confirmed'
            ],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        if (!Hash::check($data['current_password'], $user->password)) {
            throw new \Exception('Password lama salah.');
        }

        return $this->userRepository->update($user, [
            'password' => Hash::make($data['new_password'])
        ]);
    }

    public function delete($id)
    {
        $user = $this->userRepository->getById($id);
        if (!$user) {
            throw new \Exception('User tidak ditemukan');
        }

        return $this->userRepository->delete($user);
    }
}
