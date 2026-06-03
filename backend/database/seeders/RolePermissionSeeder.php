<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = config('permission.permissions', []);
        $roles       = config('permission.roles', []);

        // Create permissions
        $permissionModels = [];
        foreach (array_keys($permissions) as $name) {
            $permissionModels[$name] = Permission::firstOrCreate(
                ['name' => $name, 'guard_name' => 'api'],
                ['display_name' => $permissions[$name], 'group' => $this->groupFor($name)]
            );
        }

        // Create roles and assign permissions
        foreach ($roles as $key => $displayName) {
            $role = Role::firstOrCreate(
                ['name' => is_string($key) ? $key : $displayName, 'guard_name' => 'api'],
                ['display_name' => ucwords(str_replace(['-', '_'], ' ', $displayName))]
            );

            $perms = match ($role->name) {
                'super-admin' => $permissionModels,
                'admin'       => $permissionModels,
                'moderator'   => array_intersect_key($permissionModels, array_flip([
                    'app.view', 'app.update', 'app.archive', 'app.publish', 'app.upload',
                    'category.view', 'category.manage',
                    'user.view',
                    'audit.view',
                ])),
                'editor'      => array_intersect_key($permissionModels, array_flip([
                    'app.view', 'app.create', 'app.update', 'app.upload',
                    'category.view',
                ])),
                default       => [],
            };

            $role->syncPermissions(array_values($perms));
        }
    }

    protected function groupFor(string $permission): string
    {
        return match (true) {
            str_starts_with($permission, 'app.')      => 'apps',
            str_starts_with($permission, 'category.') => 'categories',
            str_starts_with($permission, 'user.')     => 'users',
            str_starts_with($permission, 'role.')     => 'roles',
            str_starts_with($permission, 'audit.')    => 'audit',
            str_starts_with($permission, 'settings.') => 'settings',
            default                                  => 'general',
        };
    }
}
