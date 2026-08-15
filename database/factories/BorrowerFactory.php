<?php

namespace Database\Factories;

use App\Models\Borrower;
use Illuminate\Database\Eloquent\Factories\Factory;

class BorrowerFactory extends Factory
{
    protected $model = Borrower::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => bcrypt('password'),
            'member_id' => $this->faker->unique()->numerify('MEM####'),
            'phone' => $this->faker->phoneNumber(),
            'is_active' => true,
        ];
    }
}
