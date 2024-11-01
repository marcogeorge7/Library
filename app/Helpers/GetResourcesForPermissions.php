<?php

namespace App\Helpers;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class GetResourcesForPermissions
{
    public static function generateResourcePermissions(string $resourceName): Collection
    {
        return collect([
            'viewAny' . $resourceName,
            'view' . $resourceName,
            'update' . $resourceName,
            'create' . $resourceName,
            'delete' . $resourceName,
            'forceDelete' . $resourceName,
            'restore' . $resourceName,
            'destroy' . $resourceName,
        ]);
    }

    public static function createCrudPermissions($resource): void
    {
        $permissions = self::generateResourcePermissions($resource);
        $permissions->each(
            function ($permission) use ($resource) {
                Permission::firstOrCreate([
                    'guard_name' => 'web',
                    'group' => $resource,
                    'name' => $permission,
                ]);
            }
        );
    }

    public static function syncPermissionsToSuperadmin()
    {
        $role = Role::where('name', 'Admin')->first();

        $role->givePermissionTo(Permission::all());

        if (app()->isProduction()) {
            return;
        }

        $users = User::role('Admin')->get();

        $users->each(fn ($employee) => $employee->assignRole($role));
    }

    public static function fetchResources(): Collection
    {
        $modelPath = app_path('Models');

        $files = scandir($modelPath);

        return collect($files)
            ->filter(fn ($file) => Str::endsWith($file, '.php'))
            ->map(fn ($file) => Str::replaceLast('.php', '', $file));
    }
}
