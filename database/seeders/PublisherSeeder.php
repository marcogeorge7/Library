<?php

namespace Database\Seeders;

use App\Models\Publisher;
use Illuminate\Database\Seeder;

class PublisherSeeder extends Seeder
{
    public function run(): void
    {
        $publishers = json_decode(
            file_get_contents(database_path('data/publishers.json'), true),
        );
        foreach ($publishers as $publisher) {
            Publisher::firstOrCreate(['name' => $publisher->name]);
        }
    }
}
