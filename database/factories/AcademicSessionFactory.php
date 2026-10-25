<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AcademicSession>
 */
class AcademicSessionFactory extends Factory
{
    public function definition(): array
    {
        $startYear = fake()->unique()->numberBetween(2000, 2090);

        return [
            'name' => $startYear.'-'.($startYear + 1),
            'starts_on' => Carbon::create($startYear, 4, 1),
            'ends_on' => Carbon::create($startYear + 1, 3, 31),
            'is_current' => false,
        ];
    }

    public function startingIn(int $year): static
    {
        return $this->state([
            'name' => $year.'-'.($year + 1),
            'starts_on' => Carbon::create($year, 4, 1),
            'ends_on' => Carbon::create($year + 1, 3, 31),
        ]);
    }

    public function current(): static
    {
        return $this->state(['is_current' => true]);
    }
}
