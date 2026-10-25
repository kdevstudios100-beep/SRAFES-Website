<?php

namespace App\Support\Exams;

use App\Enum\EnrollmentStatus;
use App\Enum\Permission;
use App\Models\Enrollment;
use App\Models\Exam;
use App\Models\ExamPaper;
use App\Models\SubjectAssignment;
use App\Models\TeacherAssignment;
use App\Models\User;
use App\Support\Attendance\AttendanceGroup;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Who may enter which marks and see which result sheets. Staff with "manage
 * exams" cover every class. A teacher enters marks for the subjects they are
 * assigned to teach, and a class teacher for every paper of their class or
 * section; only class teachers see the whole result sheet. A teacher
 * assignment without a section covers every section of the class.
 */
class ExamScope
{
    public function canManageAll(User $user): bool
    {
        return $this->allows($user, Permission::ManageExams);
    }

    public function canUseExams(User $user): bool
    {
        return $this->canManageAll($user) || $this->allows($user, Permission::EnterMarks);
    }

    /**
     * Classes and sections that sit at least one paper of $exam, in class order.
     *
     * @return Collection<int, AttendanceGroup>
     */
    public function groupsIn(Exam $exam): Collection
    {
        $classIds = $exam->papers()->distinct()->pluck('academic_class_id');

        return AttendanceGroup::allIn($exam->academicSession)
            ->filter(fn (AttendanceGroup $group) => $classIds->contains($group->class->id))
            ->filter(fn (AttendanceGroup $group) => $this->papersIn($exam, $group)->isNotEmpty())
            ->values();
    }

    /**
     * Papers of $exam sat by $group: the class's papers, less any subject the
     * class takes only in other sections. Subjects not set up for the class at
     * all count as taken by every section. Ordered by subject name.
     *
     * @return Collection<int, ExamPaper>
     */
    public function papersIn(Exam $exam, AttendanceGroup $group): Collection
    {
        $assigned = SubjectAssignment::query()
            ->where('academic_class_id', $group->class->id)
            ->get()
            ->groupBy('subject_id');

        return $exam->papers()
            ->where('academic_class_id', $group->class->id)
            ->with('subject')
            ->get()
            ->filter(fn (ExamPaper $paper) => ! $assigned->has($paper->subject_id) || $assigned[$paper->subject_id]
                ->contains(fn (SubjectAssignment $assignment) => $assignment->section_id === null || $assignment->section_id === $group->section?->id))
            ->sortBy(fn (ExamPaper $paper) => mb_strtolower($paper->subject->name))
            ->values();
    }

    /**
     * Mark sheets the user may open: groups with at least one paper they may enter.
     *
     * @return Collection<int, AttendanceGroup>
     */
    public function groupsFor(User $user, Exam $exam): Collection
    {
        if (! $this->canUseExams($user)) {
            return collect();
        }

        return $this->groupsIn($exam)
            ->filter(fn (AttendanceGroup $group) => $this->papersFor($user, $exam, $group)->isNotEmpty())
            ->values();
    }

    /**
     * @return Collection<int, ExamPaper>
     */
    public function papersFor(User $user, Exam $exam, AttendanceGroup $group): Collection
    {
        $papers = $this->papersIn($exam, $group);

        if ($this->canManageAll($user)) {
            return $papers;
        }

        if (! $this->allows($user, Permission::EnterMarks)) {
            return collect();
        }

        $assignments = $this->assignmentsCovering($user, $exam, $group);

        if ($assignments->contains(fn (TeacherAssignment $assignment) => $assignment->isClassTeacher())) {
            return $papers;
        }

        $subjectIds = $assignments->pluck('subject_id');

        return $papers->filter(fn (ExamPaper $paper) => $subjectIds->contains($paper->subject_id))->values();
    }

    /**
     * Result sheets the user may open: every group for staff, otherwise the
     * groups the user is class teacher of.
     *
     * @return Collection<int, AttendanceGroup>
     */
    public function resultGroupsFor(User $user, Exam $exam): Collection
    {
        if ($this->canManageAll($user)) {
            return $this->groupsIn($exam);
        }

        if (! $this->allows($user, Permission::EnterMarks)) {
            return collect();
        }

        return $this->groupsIn($exam)
            ->filter(fn (AttendanceGroup $group) => $this->assignmentsCovering($user, $exam, $group)->contains(fn (TeacherAssignment $assignment) => $assignment->isClassTeacher()))
            ->values();
    }

    public function canViewResults(User $user, Exam $exam, AttendanceGroup $group): bool
    {
        return $this->resultGroupsFor($user, $exam)->contains(fn (AttendanceGroup $allowed) => $allowed->is($group));
    }

    // Null when allowed, otherwise the reason
    public function whyCannotEnter(User $user, Exam $exam, AttendanceGroup $group, ExamPaper $paper): ?string
    {
        if (! $this->papersFor($user, $exam, $group)->contains(fn (ExamPaper $allowed) => $allowed->is($paper))) {
            return "You do not teach {$paper->subject->name} in {$group->label()}.";
        }

        if ($exam->isLocked()) {
            return $this->canManageAll($user)
                ? "{$exam->name} is locked. Unlock it under Exams to change marks."
                : "{$exam->name} is locked, so marks can no longer be changed. Ask the office if a mark is wrong.";
        }

        return null;
    }

    /**
     * Students on the mark sheet, in roll-number order: those still in the group
     * or who finished the session in it, plus anyone who left or transferred
     * out after already being given a mark.
     *
     * @return Collection<int, Enrollment>
     */
    public function roster(Exam $exam, AttendanceGroup $group): Collection
    {
        return AttendanceGroup::inRollOrder($group->enrollments()
            ->where(fn (Builder $query) => $query
                ->whereIn('status', [EnrollmentStatus::Active, EnrollmentStatus::Promoted, EnrollmentStatus::Retained, EnrollmentStatus::Graduated])
                ->orWhereHas('examMarks', fn (Builder $query) => $query->whereIn('exam_paper_id', $exam->papers()->select('id'))))
            ->with('student')
            ->get());
    }

    /**
     * The user's assignments in the exam's session that cover $group.
     *
     * @return Collection<int, TeacherAssignment>
     */
    private function assignmentsCovering(User $user, Exam $exam, AttendanceGroup $group): Collection
    {
        return TeacherAssignment::query()
            ->where('user_id', $user->id)
            ->where('academic_session_id', $exam->academic_session_id)
            ->where('academic_class_id', $group->class->id)
            ->where(fn (Builder $query) => $query->whereNull('section_id')->when($group->section, fn (Builder $query) => $query->orWhere('section_id', $group->section->id)))
            ->get();
    }

    private function allows(User $user, Permission $permission): bool
    {
        return $user->is_active && $user->checkPermissionTo($permission->value);
    }
}
