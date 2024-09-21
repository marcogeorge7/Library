<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = json_decode(
            file_get_contents(
                database_path("data/categories.json")), true);

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
