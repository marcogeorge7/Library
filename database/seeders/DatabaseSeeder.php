<?php

namespace Database\Seeders;

use App\Helpers\GetResourcesForPermissions;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        Role::create([
            'name' => 'Admin',
        ]);

       $user = User::firstOrCreate([
            'name' => 'Admin',
            'email' => 'admin@library.com',
            'password' => bcrypt('123456789'),
        ]);

       $user->assignRole('Admin');

       $resources = GetResourcesForPermissions::fetchResources();
        $resources->each(function ($resource) {
            GetResourcesForPermissions::createCrudPermissions($resource);
            GetResourcesForPermissions::syncPermissionsToSuperadmin();
        });

        $this->call([
            AuthorSeeder::class,
            CategorySeeder::class
        ]);
    }
}
