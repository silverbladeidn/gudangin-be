<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cache roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Daftar permissions
        $resources = [
            'dashboard'       => ['view'],
            'homeuser'        => ['view'],
            'inventaris'      => ['view', 'create', 'edit', 'delete'],
            'inventory'       => ['view', 'create', 'edit', 'delete'],
            'stocknote'       => ['view'],
            'requestnote'     => ['view'],
            'approvalnote'    => ['view', 'delete'],
            'users'           => ['view', 'create', 'edit', 'delete'],
            'email_settings'  => ['view', 'edit'],
        ];

        // Create permissions
        foreach ($resources as $res => $actions) {
            foreach ($actions as $act) {
                Permission::updateOrCreate(
                    ['name' => "{$act}_{$res}"],
                    ['guard_name' => 'web']
                );
            }
        }

        // Assign permissions to roles
        $superadmin = Role::where('name', 'Superadmin')->first();
        $admin = Role::where('name', 'Admin')->first();
        $user = Role::where('name', 'User')->first();

        // Superadmin - semua permissions
        if ($superadmin) {
            $superadmin->givePermissionTo(Permission::all());
        }

        // Admin permissions
        if ($admin) {
            $adminPermissions = [
                'view_dashboard',
                'view_inventory',
                'create_inventory',
                'edit_inventory',
                'delete_inventory',
                'view_stocknote',
                'view_approvalnote',
                'delete_approvalnote', // ✅ Fixed typo: approvalnote
            ];
            $admin->givePermissionTo($adminPermissions);
        }

        // User permissions  
        if ($user) {
            $userPermissions = [
                'view_homeuser',
                'view_inventaris',
                'create_inventaris',
                'edit_inventaris',
                'delete_inventaris',
                'view_requestnote',
            ];
            $user->givePermissionTo($userPermissions);
        }
    }
}
