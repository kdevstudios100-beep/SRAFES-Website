<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One student's mark on one paper: whole marks obtained, or absent.
 * Saved only through App\Support\Exams\MarkSheet.
 */
class ExamMark extends Model
{
    protected $fillable = [
        'exam_paper_id',
        'enrollment_id',
        'obtained_marks',
        'is_absent',
        'remarks',
        'recorded_by',
    ];

    protected function casts(): array
    {
        return [
            'obtained_marks' => 'integer',
            'is_absent' => 'boolean',
        ];
    }

    public function paper(): BelongsTo
    {
        return $this->belongsTo(ExamPaper::class, 'exam_paper_id');
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
