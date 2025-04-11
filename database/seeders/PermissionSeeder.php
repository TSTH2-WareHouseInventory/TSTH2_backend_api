<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

use function PHPSTORM_META\map;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            'manage_permissions',

            'create_user',
            'update_user',
            'view_user',
            'delete_user',

            'create_role',
            'update_role',
            'view_role',
            'delete_role',

            'create_barang',
            'update_barang',
            'view_barang',
            'delete_barang',

            'create_gudang',
            'update_gudang',
            'view_gudang',
            'delete_gudang',

            'create_satuan',
            'update_satuan',
            'view_satuan',
            'delete_satuan',

            'create_jenis_barang',
            'update_jenis_barang',
            'view_jenis_barang',
            'delete_jenis_barang',

            'create_transaction_type',
            'update_transaction_type',
            'view_transaction_type',
            'delete_transaction_type',

            'create_transaction',
            'view_transaction',

            'create_category_barang',
            'update_category_barang',
            'view_category_barang',
            'delete_category_barang',

            'create_barang_status',
            'update_barang_status',
            'view_barang_status',
            'delete_barang_status',

        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'api']);
        }
    }
}
