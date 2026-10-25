<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AcademicClass>
 */
class AcademicClassFactory extends Factory
{
    public function definition(): array
    {
        $position = fake()->unique()->numberBetween(1, 500);

        return [
            'name' => "Class {$position}",
            'sort_order' => $position,
        ];
    }
}
