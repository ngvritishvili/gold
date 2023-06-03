<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->name,
            'price' => $this->faker->randomFloat('2',0,10000),
            'quantity' => $this->faker->randomNumber(1,50),
            'user_id' => User::inRandomOrder()->first(),
        ];
    }
}
