<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A subject sat by one class in an exam. Every section of the class that
 * takes the subject sits the same paper.
 */
class ExamPaper extends Model
{
    protected $fillable = [
        'exam_id',
        'academic_class_id',
        'subject_id',
        'total_marks',
        'pass_marks',
    ];

    protected function casts(): array
    {
        return [
            'total_marks' => 'integer',
            'pass_marks' => 'integer',
        ];
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function academicClass(): BelongsTo
    {
        return $this->belongsTo(AcademicClass::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function marks(): HasMany
    {
        return $this->hasMany(ExamMark::class);
    }

    public function passes(?ExamMark $mark): bool
    {
        return $mark !== null && ! $mark->is_absent && $mark->obtained_marks !== null && $mark->obtained_marks >= $this->pass_marks;
    }
}
