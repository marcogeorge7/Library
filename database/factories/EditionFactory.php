<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\Edition;
use App\Models\Publisher;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class EditionFactory extends Factory
{
    protected $model = Edition::class;

    public function definition(): array
    {
        return [
            'partCode' => $this->faker->randomNumber(),
            'publish_year' => $this->faker->word(),
            'lang' => $this->faker->word(),
            'cover' => $this->faker->word(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'book_id' => Book::factory(),
            'publisher_id' => Publisher::factory(),
        ];
    }
}
