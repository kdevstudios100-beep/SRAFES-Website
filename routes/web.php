<?php

use App\Models\Announcement;
use App\Models\JobPost;
use Illuminate\Support\Facades\Route;
use Kenepa\TranslationManager\Http\Middleware\SetLanguage;

Route::redirect('/login', '/admin/login')->name('login');

Route::middleware(SetLanguage::class)->group(function () {
    Route::get('/', function () {
        return view('welcome');
    });
    Route::get('/about', function () {
        return view('about');
    });
    Route::get('/scholarships', function () {
        return view('scholarships');
    });
    Route::get('/gallery', function () {
        return view('gallery');
    });
    Route::get('/contact', function () {
        return view('contact');
    })->name('contact');
    Route::get('/examination-results', function () {
        return view('examination-results');
    });
    Route::get('/announcement/{announcement}', function (Announcement $announcement) {
        return view('announcement', ['announcement' => $announcement]);
    })->name('announcement.show');
    Route::get('/careers', function () {
        $jobPosts = JobPost::open()
            ->orderBy('application_deadline', 'asc')
            ->get();

        return view('careers', ['jobPosts' => $jobPosts]);
    })->name('careers');
    Route::get('/careers/{jobPost}', function (JobPost $jobPost) {
        // Logged-in admins can still preview closed posts from the panel
        abort_unless($jobPost->isOpen() || auth()->check(), 404);

        return view('career-detail', ['jobPost' => $jobPost]);
    })->name('careers.show');
});
