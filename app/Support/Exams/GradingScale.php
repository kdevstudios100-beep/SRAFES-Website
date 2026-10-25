<?php

namespace App\Support\Exams;

/**
 * Percentage to grade, from config('srafes.exams.grades').
 */
class GradingScale
{
    public function grade(float $percent): string
    {
        foreach ($this->bands() as $grade => $minimum) {
            if ($percent >= $minimum) {
                return (string) $grade;
            }
        }

        return $this->failGrade();
    }

    public function failGrade(): string
    {
        return (string) config('srafes.exams.fail_grade', 'F');
    }

    // Pass mark suggested for a new paper out of $totalMarks
    public function suggestedPassMarks(int $totalMarks): int
    {
        return (int) ceil($totalMarks * (int) config('srafes.exams.pass_percent', 33) / 100);
    }

    /**
     * @return array<string, int> grade => lowest percentage, highest grade first
     */
    public function bands(): array
    {
        $bands = (array) config('srafes.exams.grades', []);
        arsort($bands);

        return $bands;
    }

    /**
     * "A1 80%+ · A 70-79% · ... · F below 33%", for result card footers.
     */
    public function legend(): string
    {
        $parts = [];
        $ceiling = null;

        foreach ($this->bands() as $grade => $minimum) {
            $parts[] = $grade.' '.($ceiling === null ? "{$minimum}%+" : $minimum.'-'.($ceiling - 1).'%');
            $ceiling = $minimum;
        }

        if ($ceiling !== null) {
            $parts[] = $this->failGrade()." below {$ceiling}%";
        }

        return implode(' · ', $parts);
    }
}
