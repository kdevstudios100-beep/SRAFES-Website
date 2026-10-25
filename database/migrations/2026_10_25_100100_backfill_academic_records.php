<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const NUMBER_WORDS = [
        'one' => 1, 'two' => 2, 'three' => 3, 'four' => 4, 'five' => 5, 'six' => 6,
        'seven' => 7, 'eight' => 8, 'nine' => 9, 'ten' => 10, 'eleven' => 11, 'twelve' => 12,
    ];

    /**
     * Carry existing records into the new structure without changing any
     * student or result: classes get a ladder position from their name, and
     * every student who has a class is enrolled in the current session.
     * Written with the query builder so later model changes can't alter it.
     */
    public function up(): void
    {
        DB::transaction(function () {
            foreach (DB::table('academic_classes')->whereNull('sort_order')->get() as $class) {
                DB::table('academic_classes')->where('id', $class->id)->update(['sort_order' => $this->ladderPosition($class->name)]);
            }

            $students = DB::table('students')->whereNotNull('academic_class_id')->orderBy('id')->get();

            if ($students->isEmpty() || DB::table('enrollments')->exists()) {
                return;
            }

            $sessionId = $this->currentSessionId();
            $now = now();

            foreach ($students as $student) {
                DB::table('enrollments')->insert([
                    'student_id' => $student->id,
                    'academic_session_id' => $sessionId,
                    'academic_class_id' => $student->academic_class_id,
                    'section_id' => null,
                    // Inactive students keep their last class as history; the date they left is unknown
                    'status' => $student->is_active ? 'active' : 'left',
                    'enrolled_on' => null,
                    'ended_on' => null,
                    'notes' => 'Recorded from the student\'s existing class when academic sessions were introduced.',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        });
    }

    public function down(): void
    {
        // The tables holding this data are dropped by the create_academic_structure_tables migration
    }

    private function ladderPosition(string $name): ?int
    {
        $name = strtolower($name);

        if (preg_match('/\b(\d{1,2})\b/', $name, $match)) {
            return (int) $match[1];
        }

        foreach (self::NUMBER_WORDS as $word => $number) {
            if (preg_match("/\\b{$word}\\b/", $name)) {
                return $number;
            }
        }

        return null;
    }

    private function currentSessionId(): int
    {
        $existing = DB::table('academic_sessions')->where('is_current', true)->value('id');

        if ($existing) {
            return $existing;
        }

        $startMonth = (int) config('srafes.academics.session_start_month', 4);
        $today = Carbon::today();
        $startYear = $today->month >= $startMonth ? $today->year : $today->year - 1;
        $startsOn = Carbon::create($startYear, $startMonth, 1);
        $endsOn = $startsOn->copy()->addYear()->subDay();

        return DB::table('academic_sessions')->insertGetId([
            'name' => $startsOn->year === $endsOn->year ? (string) $startsOn->year : "{$startsOn->year}-{$endsOn->year}",
            'starts_on' => $startsOn->toDateString(),
            'ends_on' => $endsOn->toDateString(),
            'is_current' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
};
