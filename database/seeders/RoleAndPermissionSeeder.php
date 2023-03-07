<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $role = Role::create(['name' => 'admin']);

        $permissions = [
            'manage.category',
            'manage.product',
            'manage.order',
        ];

        foreach ($permissions as $permission) {
            $permission = Permission::create([
                'name' => $permission,
                'guard_name' => 'api',
            ]);
        }

        $role->syncPermissions($permissions);
    }
}
