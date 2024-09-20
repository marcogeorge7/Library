<?php

namespace App\Console\Commands;

use App\Helpers\GetResourcesForPermissions;
use App\Models\User;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class GenerateResourcePermissionsCommand extends Command
{
    protected $signature = 'generate:resource-permissions {modelName}';

    protected $description = 'Command description';

    public function handle(): void
    {
        $modelName = $this->argument('modelName');
        $this->createCrudPermissions($modelName);
        $this->syncPermissionsToSuperadmin();
        $this->info("Generating {$modelName} Permissions");

    }

    protected function createCrudPermissions($resource)
    {
        $permissions = GetResourcesForPermissions::generateResourcePermissions($resource);
        $permissions->each(
            function ($permission) use ($resource) {
                Permission::firstOrCreate([
                    'guard_name' => "web",
                    'group' => $resource,
                    'name' => $permission,
                ]);
            }
        );
    }

    private function syncPermissionsToSuperadmin()
    {
        $role = Role::where('name', "Admin")->first();

        $role->givePermissionTo(Permission::all());

        if (app()->isProduction()) {
            return;
        }

        $users =User::role("Admin")->get();

        $users->each(fn ($employee) => $employee->assignRole($role));
    }
}
