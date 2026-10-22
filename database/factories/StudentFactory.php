<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Student>
 */
class StudentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'admission_number' => str()->random(),
            'first_name' => fake()->firstName,
            'last_name' => fake()->lastName,
            'academic_class_id' => null,
            'is_active' => fake()->boolean,
            'has_scholarship' => fake()->boolean,
        ];
    }
}
