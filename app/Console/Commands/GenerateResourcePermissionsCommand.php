<?php

namespace App\Console\Commands;

use App\Helpers\GetResourcesForPermissions;
use Illuminate\Console\Command;

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
