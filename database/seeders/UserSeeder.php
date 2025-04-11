<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
       $superAdminRole = Role::where('name', 'superadmin')->first();
       $adminRole = Role::where('name', 'admin')->first();
       $operatorRole = Role::where('name', 'operator')->first();

       $superAdmin = User::create([
           'name' => 'Super Admin',
           'email' => 'superadmin@example.com',
           'password' => Hash::make('password123'),
           'role_id' => $superAdminRole->id,
       ]);
       $superAdmin->assignRole($superAdminRole);

       // Buat user admin
       $admin = User::create([
           'name' => 'Admin',
           'email' => 'admin@example.com',
           'password' => Hash::make('password123'),
           'role_id' => $adminRole->id,
       ]);
       $admin->assignRole($adminRole);

       // Buat user operator
       for ($i = 1; $i <= 5; $i++) {
        $operator = User::create([
            'name' => "Operator$i",
            'email' => "operator$i@example.com",
            'password' => Hash::make('password123'),
            'role_id' => $operatorRole->id ?? null,
        ]);

        $operator->assignRole($operatorRole);
    }
}
}
