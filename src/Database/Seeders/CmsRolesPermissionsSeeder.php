<?php

namespace CMS\SiteManager\Database\Seeders;

use Illuminate\Database\Seeder;
use CMS\SiteManager\Models\CmsKit\Admin;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CmsRolesPermissionsSeeder extends Seeder
{
    protected function filterExistingPermissions(array $permissions): array
    {
        $existingPermissions = Permission::where('guard_name', 'cms')
            ->pluck('name')
            ->all();

        return array_values(array_intersect($permissions, $existingPermissions));
    }

    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Create permissions for all enabled modules
        $modules = config('cms-kit.common.modules', []);
        $permissionDefaults = config('cms-kit.permissions.defaults', ['view', 'edit']);

        foreach ($modules as $moduleKey => $enabled) {
            if (!$enabled)
                continue;
            foreach ($permissionDefaults as $action) {
                Permission::updateOrCreate([
                    'name' => $moduleKey . '.' . $action,
                    'guard_name' => 'cms'
                ]);
            }
        }

        // 2. Create extra permissions for UI management
        $extraPermissions = [
            'users.create', 'users.view', 'users.edit', 'users.delete', 
            'roles.create', 'roles.view', 'roles.edit', 'roles.delete', 
            'permissions.create', 'permissions.view', 'permissions.edit', 'permissions.delete', 
            'sitemap.view', 'sitemap.edit',
            'robots-txt.view', 'robots-txt.edit',
            'llms-txt.view', 'llms-txt.edit',
            'banners.view', 'banners.edit', 'banners.create', 'banners.delete', 
            'faqs.view', 'faqs.edit', 'faqs.create', 'faqs.delete', 
            'enquiries.view', 'enquiries.show', 'enquiries.delete', 'enquiries.export',
            'locations.view', 'locations.edit', 'locations.create', 'locations.delete',
            'brands.view', 'brands.edit', 'brands.create', 'brands.delete',
            'newsletter.view', 'newsletter.delete',
            'blogs.view', 'blogs.create', 'blogs.edit', 'blogs.delete',
            'careers.view', 'careers.create', 'careers.edit', 'careers.delete', 'careers.show', 'careers.export',
            'url-redirects.view', 'url-redirects.edit','url-miss-logs.view', 'url-miss-logs.edit','robots-txt.view','robots-txt.edit','llms-txt.view','llms-txt.edit',
        ];

        foreach ($extraPermissions as $perm) {
            Permission::updateOrCreate(['name' => $perm, 'guard_name' => 'cms']);
        }

        // 3. Create Roles and assign permissions from config
        $rolesConfig = config('cms-kit.permissions.roles', []);
        foreach ($rolesConfig as $roleSlug => $roleData) {
            $role = Role::updateOrCreate(
            ['name' => $roleSlug, 'guard_name' => 'cms'],
                // Add name as a localized label if needed (optional)
            );

            if ($roleData['permissions'] === '*') {
                $role->syncPermissions(Permission::where('guard_name', 'cms')->get());
            }
            else {
                $role->syncPermissions($this->filterExistingPermissions($roleData['permissions']));
            }
        }

        // 4. Create Default Admins from config
        $usersConfig = config('cms-kit.permissions.users', []);
        foreach ($usersConfig as $userData) {
            $admin = Admin::updateOrCreate(
            ['email' => $userData['email']],
            [
                'name' => $userData['name'],
                'password' => Hash::make($userData['password']),
                'is_active' => true,
            ]
            );

            if (isset($userData['role'])) {
                $admin->syncRoles([$userData['role']]);
            }
        }
    }
}

