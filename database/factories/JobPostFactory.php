<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\JobPost>
 */
class JobPostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->jobTitle(),
            'content' => fake()->paragraphs(5, true),
            'salary_range' => fake()->boolean(70) ? fake()->randomElement([
                '$30,000 - $50,000',
                '$50,000 - $70,000',
                '$70,000 - $90,000',
                '$90,000 - $120,000',
                'Competitive salary',
                'Negotiable'
            ]) : null,
            'application_deadline' => fake()->boolean(80) ? fake()->dateTimeBetween('now', '+3 months') : null,
            'is_active' => fake()->boolean(85),
        ];
    }
}
