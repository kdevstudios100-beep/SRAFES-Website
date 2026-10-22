<?php

namespace App\Models;

use App\Enum\ExaminationResultStatus;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class ExaminationResult extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    protected $fillable = [
        'student_id',
        'academic_class_id',
        'year',
        'detail',
        'total_marks',
        'obtained_marks',
        'status',
        'grade',
    ];

    public function getCasts(): array
    {
        return [
            'status' => ExaminationResultStatus::class,
        ];
    }

    public function academicClass(): BelongsTo
    {
        return $this->belongsTo(AcademicClass::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function percentage(): Attribute
    {
        return Attribute::get(function () {
            if ($this->total_marks == 0 || $this->obtained_marks == 0) {
                return 0;
            }

            $percentage = $this->obtained_marks / $this->total_marks * 100;

            return round($percentage, 2);
        })->shouldCache();
    }

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('result_file')
            // Private disk: files are only reachable through authorized downloads or short-lived signed URLs
            ->useDisk('local')
            ->singleFile();
    }
}
