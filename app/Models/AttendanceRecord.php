<?php

namespace App\Models;

use App\Enum\AttendanceStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One student's attendance on one day, for the placement (enrollment) they
 * had that day. Saved only through App\Support\Attendance\AttendanceRegister.
 */
class AttendanceRecord extends Model
{
    protected $fillable = [
        'enrollment_id',
        'date',
        'status',
        'remarks',
        'recorded_by',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'status' => AttendanceStatus::class,
        ];
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
