<?php

namespace App\Support\Attendance;

use App\Models\AcademicClass;
use App\Models\AcademicSession;
use App\Models\Enrollment;
use App\Models\Section;
use Illuminate\Database\Eloquent\Builder;

/**
 * One attendance register: a class (or one of its sections) in a session.
 * Classes without sections are registered as a whole.
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
}
