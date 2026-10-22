<?php

use App\Models\AcademicClass;
use App\Models\Announcement;
use App\Models\Employee;
use App\Models\ExaminationResult;
use App\Models\JobApplication;
use App\Models\JobPost;
use App\Models\SiteSetting;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\MassAssignmentException;
use Illuminate\Database\Eloquent\Model;

it('keeps mass assignment protection switched on', function () {
    expect(Model::isUnguarded())->toBeFalse();
});

it('declares explicit fillable attributes that never include keys or tokens', function (string $model) {
    $instance = new $model;

    expect($instance->getFillable())->not->toBeEmpty()
        ->and($instance->isFillable('id'))->toBeFalse()
        ->and($instance->isFillable('remember_token'))->toBeFalse()
        ->and($instance->isFillable('email_verified_at'))->toBeFalse();
})->with([
    AcademicClass::class, Announcement::class, Employee::class, ExaminationResult::class,
    JobApplication::class, JobPost::class, SiteSetting::class, Student::class, User::class,
]);

it('rejects attributes that are not fillable instead of saving them', function () {
    User::create([
        'name' => 'Someone',
        'email' => 'someone@example.org',
        'password' => 'long-password-1',
        'email_verified_at' => now(),
    ]);
})->throws(MassAssignmentException::class);
