<?php

namespace Database\Factories;

use App\Models\Copy;
use App\Models\Edition;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class CopyFactory extends Factory
{
    protected $model = Copy::class;

    public function definition(): array
    {
        return [
            'barcode' => $this->faker->word(),
            'is_borrowed' => $this->faker->boolean(),
            'is_printed' => $this->faker->boolean(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'edition_id' => Edition::factory(),
        ];
    }
}
