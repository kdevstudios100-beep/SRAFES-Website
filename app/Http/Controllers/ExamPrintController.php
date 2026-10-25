<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Support\Attendance\AttendanceGroup;
use App\Support\Exams\ExamScope;
use App\Support\Exams\GradingScale;
use App\Support\Exams\ResultSheet;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Printable pages for one class or section's exam results, opened from the
 * Exam results page in either panel. Same access as the result sheet.
 */
class ExamPrintController extends Controller
{
    public function __construct(
        private readonly ExamScope $scope,
        private readonly ResultSheet $sheet,
        private readonly GradingScale $scale,
    ) {}

    // One result card per student, one per page; ?enrollment= narrows it to one student
    public function cards(Request $request, Exam $exam): View
    {
        $data = $this->resolve($request, $exam);

        if ($request->filled('enrollment')) {
            $data['rows'] = $data['rows']->filter(fn (array $row) => $row['enrollment']->id === (int) $request->query('enrollment'))->values();
            abort_if($data['rows']->isEmpty(), 404);
        }

        return view('exams.result-cards', $data);
    }

    // The whole class on one landscape sheet
    public function sheet(Request $request, Exam $exam): View
    {
        return view('exams.result-sheet', $this->resolve($request, $exam));
    }

    private function resolve(Request $request, Exam $exam): array
    {
        $group = AttendanceGroup::fromKey((string) $request->query('group'));

        abort_if($group === null || $group->session->id !== $exam->academic_session_id, 404);
        abort_unless($this->scope->canViewResults($request->user(), $exam, $group), 403);

        $papers = $this->scope->papersIn($exam, $group);
        $rows = $this->sheet->forGroup($exam, $group, $papers);

        return [
            'exam' => $exam->loadMissing('academicSession'),
            'group' => $group,
            'papers' => $papers,
            'rows' => $rows,
            'sheet' => $this->sheet,
            'legend' => $this->scale->legend(),
            'classSize' => $rows->count(),
        ];
    }
}
