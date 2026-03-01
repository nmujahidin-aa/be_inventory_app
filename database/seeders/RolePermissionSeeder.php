<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'auth.login', 'auth.logout',

            'users.view', 'users.create', 'users.update', 'users.deactivate',

            'items.view', 'items.create', 'items.update', 'items.deactivate',
            'categories.manage', 'units.manage', 'vendors.manage',

            'requests.view-own', 'requests.view-all',
            'requests.create', 'requests.update-own', 'requests.delete-own',
            'requests.submit', 'requests.approve', 'requests.reject',

            'purchase-orders.view', 'purchase-orders.create',
            'purchase-orders.update', 'purchase-orders.delete',
            'purchase-orders.submit', 'purchase-orders.send',
            'purchase-orders.approve', 'purchase-orders.reject',
            'purchase-orders.confirm', 'purchase-orders.cancel',

            'receivings.view', 'receivings.create',
            'receivings.add-item', 'receivings.complete', 'receivings.return',

            'inventory.view', 'inventory.view-movements', 'inventory.view-low-stock',

            'stock-opnames.view', 'stock-opnames.create', 'stock-opnames.update',
            'stock-opnames.add-item', 'stock-opnames.submit',
            'stock-opnames.approve', 'stock-opnames.reject',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'api']);
        }

        $adminGudang = Role::firstOrCreate(['name' => 'admin_gudang','guard_name' => 'api']);
        $spv         = Role::firstOrCreate(['name' => 'spv','guard_name' => 'api']);
        $technician  = Role::firstOrCreate(['name' => 'technician','guard_name' => 'api']);

        $adminGudang->syncPermissions([
            'auth.login', 'auth.logout',
            'users.view', 'users.create', 'users.update', 'users.deactivate',
            'items.view', 'items.create', 'items.update', 'items.deactivate',
            'categories.manage', 'units.manage', 'vendors.manage',
            'requests.view-all',
            'purchase-orders.view', 'purchase-orders.create', 'purchase-orders.update',
            'purchase-orders.delete', 'purchase-orders.submit', 'purchase-orders.send',
            'purchase-orders.confirm', 'purchase-orders.cancel',
            'receivings.view', 'receivings.create', 'receivings.add-item', 'receivings.complete', 'receivings.return',
            'inventory.view', 'inventory.view-movements', 'inventory.view-low-stock',
            'stock-opnames.view', 'stock-opnames.create', 'stock-opnames.update',
            'stock-opnames.add-item', 'stock-opnames.submit',
        ]);

        $spv->syncPermissions([
            'auth.login', 'auth.logout',
            'items.view',
            'requests.view-all', 'requests.approve', 'requests.reject',
            'purchase-orders.view', 'purchase-orders.approve', 'purchase-orders.reject',
            'inventory.view', 'inventory.view-movements',
            'stock-opnames.view', 'stock-opnames.approve', 'stock-opnames.reject',
        ]);

        $technician->syncPermissions([
            'auth.login', 'auth.logout',
            'items.view',
            'requests.view-own', 'requests.create', 'requests.update-own',
            'requests.delete-own', 'requests.submit',
        ]);

        $this->command->info('Roles & permissions seeded successfully.');
    }
}