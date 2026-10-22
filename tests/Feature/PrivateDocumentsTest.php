<?php

use App\Models\ExaminationResult;
use App\Models\JobApplication;
use App\Models\JobPost;
use App\Models\Student;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

beforeEach(function () {
    // Keep `serve` so the fake private disk still answers signed URLs like the real one
    Storage::fake('local', ['serve' => true]);
    Storage::fake('public');
    Storage::fake('filament');
});

it('stores applicant documents on the private disk', function () {
    $application = JobApplication::factory()->for(JobPost::factory())->create();

    $resume = $application->addMedia(UploadedFile::fake()->create('cv.pdf', 10, 'application/pdf'))->toMediaCollection('resume');
    $letter = $application->addMedia(UploadedFile::fake()->create('letter.pdf', 10, 'application/pdf'))->toMediaCollection('cover_letter');

    expect($resume->disk)->toBe('local')->and($letter->disk)->toBe('local');
    Storage::disk('local')->assertExists($resume->getPathRelativeToRoot());
    Storage::disk('public')->assertMissing($resume->getPathRelativeToRoot());
});

it('stores examination result files on the private disk', function () {
    $result = ExaminationResult::create([
        'student_id' => Student::factory()->create()->id,
        'total_marks' => 100,
        'obtained_marks' => 80,
    ]);

    $media = $result->addMedia(UploadedFile::fake()->create('result.pdf', 10, 'application/pdf'))->toMediaCollection('result_file');

    expect($media->disk)->toBe('local');
    Storage::disk('local')->assertExists($media->getPathRelativeToRoot());
    Storage::disk('filament')->assertMissing($media->getPathRelativeToRoot());
});

it('serves private files only through valid, unexpired signed URLs', function () {
    Storage::disk('local')->put('1/private.pdf', 'secret');

    $this->get('/storage/1/private.pdf')->assertForbidden();

    // Built exactly like LocalFilesystemAdapter::temporaryUrl (the fake disk returns a placeholder URL instead)
    $signedUrl = fn ($expires) => url(URL::temporarySignedRoute('storage.local', $expires, ['path' => '1/private.pdf'], absolute: false));

    $signed = $signedUrl(now()->addMinutes(5));
    $this->get($signed)->assertOk();
    $this->get($signed.'0')->assertForbidden();

    $this->get($signedUrl(now()->subMinute()))->assertForbidden();
});
