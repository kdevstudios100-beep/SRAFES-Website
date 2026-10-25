<?php

namespace App\Support\Attendance;

use App\Models\AcademicClass;
use App\Models\AcademicSession;
use App\Models\Enrollment;
use App\Models\Section;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * One attendance register: a class (or one of its sections) in a session.
 * Classes without sections are registered as a whole. Exams use the same
 * groups for mark sheets and result sheets.
 */
final class AttendanceGroup
{
    public function __construct(
        public readonly AcademicSession $session,
        public readonly AcademicClass $class,
        public readonly ?Section $section = null,
    ) {}

    // "session:class:section", safe to pass through the browser and resolve again with fromKey()
    public function key(): string
    {
        return "{$this->session->id}:{$this->class->id}:".($this->section?->id ?? 0);
    }

    public static function fromKey(string $key): ?self
    {
        if (! preg_match('/^(\d+):(\d+):(\d+)$/', $key, $match)) {
            return null;
        }

        $session = AcademicSession::find($match[1]);
        $class = AcademicClass::find($match[2]);
        $section = $match[3] === '0' ? null : Section::find($match[3]);

        if (! $session || ! $class || ($match[3] !== '0' && (! $section || $section->academic_class_id !== $class->id))) {
            return null;
        }

        return new self($session, $class, $section);
    }

    public function label(): string
    {
        return $this->class->name.($this->section ? ' - '.$this->section->name : '');
    }

    // Every placement ever made in this register's session, class and section
    public function enrollments(): Builder
    {
        return Enrollment::query()
            ->where('academic_session_id', $this->session->id)
            ->where('academic_class_id', $this->class->id)
            ->when(
                $this->section,
                fn (Builder $query) => $query->where('section_id', $this->section->id),
                fn (Builder $query) => $query->whereNull('section_id'),
            );
    }

    public function is(self $other): bool
    {
        return $this->key() === $other->key();
    }

    /**
     * Every class (or section) that has had at least one placement in $session, in class order.
     *
     * @return Collection<int, self>
     */
    public static function allIn(AcademicSession $session): Collection
    {
        $used = Enrollment::query()
            ->where('academic_session_id', $session->id)
            ->select(['academic_class_id', 'section_id'])
            ->distinct()
            ->get();

        $classes = AcademicClass::query()->whereIn('id', $used->pluck('academic_class_id'))->ordered()->get();
        $sections = Section::query()->whereIn('id', $used->pluck('section_id')->filter())->get()->keyBy('id');

        return $classes->flatMap(fn (AcademicClass $class) => $used
            ->where('academic_class_id', $class->id)
            ->sortBy(fn ($row) => $row->section_id ? $sections[$row->section_id]->name : '')
            ->map(fn ($row) => new self($session, $class, $row->section_id ? $sections[$row->section_id] : null)))
            ->values();
    }

    /**
     * Roll-number order, students without a roll number last, then by name.
     *
     * @param  Collection<int, Enrollment>  $enrollments  with the student loaded
     * @return Collection<int, Enrollment>
     */
    public static function inRollOrder(Collection $enrollments): Collection
    {
        return $enrollments
            ->sortBy([
                fn (Enrollment $a, Enrollment $b) => strnatcasecmp($a->roll_number ?? "\u{FFFF}", $b->roll_number ?? "\u{FFFF}"),
                fn (Enrollment $a, Enrollment $b) => strnatcasecmp($a->student->name, $b->student->name),
            ])
            ->values();
    }
}
