<?php

namespace Database\Factories;

use App\Enum\EmployeeRole;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Employee>
 */
class EmployeeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name,
            'role' => fake()->randomElement(EmployeeRole::cases()),
            'is_featured' => fake()->boolean,
            'facebook_url' => fake()->boolean ? 'https://fb.com' : null,
            'x_url' => fake()->boolean ? 'https://x.com' : null,
        ];
    }
}
