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
        GetResourcesForPermissions::createCrudPermissions($modelName);
        GetResourcesForPermissions::syncPermissionsToSuperadmin();
        $this->info("Generating {$modelName} Permissions");

    }
}
