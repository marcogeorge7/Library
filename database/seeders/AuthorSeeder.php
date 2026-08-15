<?php

namespace Database\Seeders;

use App\Models\Author;
use Illuminate\Database\Seeder;

class AuthorSeeder extends Seeder
{
    public function run(): void
    {
        $authors = json_decode(
            file_get_contents(
                database_path('data/authors.json')), true);

        foreach ($authors as $author) {
            Author::firstOrCreate(['name' => $author['name']], $author);
        }

    }
}
