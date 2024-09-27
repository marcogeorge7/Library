<?php

namespace Database\Factories;

use App\Models\Revisor;
use Illuminate\Database\Eloquent\Factories\Factory;

class RevisorFactory extends Factory
{
    protected $model = Revisor::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
        ];
    }
}
