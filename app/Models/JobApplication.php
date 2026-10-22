<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class JobApplication extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    protected $fillable = [
        'job_post_id',
        'name',
        'phone',
        'email',
    ];

    public function jobPost(): BelongsTo
    {
        return $this->belongsTo(JobPost::class);
    }

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('resume')
            // Private disk: applicant documents are only reachable through short-lived signed URLs
            ->useDisk('local')
            ->singleFile();

        $this
            ->addMediaCollection('cover_letter')
            ->useDisk('local')
            ->singleFile();
    }
}
