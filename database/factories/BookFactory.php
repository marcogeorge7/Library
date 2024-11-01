<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\Category;
use App\Models\Revisor;
use App\Models\Series;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class BookFactory extends Factory
{
    protected $model = Book::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'category_id' => Category::factory(),
            'revisor_id' => Revisor::factory(),
            'series_id' => Series::factory(),
        ];
    }
}
