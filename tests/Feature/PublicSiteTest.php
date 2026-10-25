<?php

use App\Models\Announcement;
use App\Models\JobPost;
use App\Models\SiteSetting;
use App\Models\Student;
use Illuminate\Support\Carbon;

it('renders every public page', function (string $page) {
    SiteSetting::create(['settings' => ['name' => 'SRAFES', 'email' => 'info@example.org']]);

    $this->get($page)->assertOk();
})->with(['/', '/about', '/scholarships', '/gallery', '/contact', '/examination-results', '/careers']);

it('renders public pages on an empty database', function () {
    $this->get('/')->assertOk();
    $this->get('/gallery')->assertOk();
});

it('sends security headers', function () {
    $this->get('/')
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
        ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
});

it('shows an announcement page', function () {
    $announcement = Announcement::create([
        'title' => 'Sports Day',
        'description' => 'Annual sports',
        'is_recurring' => false,
        'start_date' => now(),
    ]);

    $this->get(route('announcement.show', $announcement))->assertOk()->assertSee('Sports Day');
});

it('shows recurring announcements on the right days, including ranges over new year', function (string $date, array $expected) {
    $make = fn (string $title, bool $recurring, string $start, ?string $end) => Announcement::create([
        'title' => $title, 'description' => '-', 'is_recurring' => $recurring, 'start_date' => $start, 'end_date' => $end,
    ]);

    $make('spring', true, '2022-03-20', '2022-04-25');
    $make('newyear', true, '2022-12-25', '2023-01-05');
    $make('oneday', true, '2022-08-14', null);
    $make('once', false, '2026-04-01', '2026-04-10');

    Carbon::setTestNow($date.' 10:00:00');

    expect(Announcement::today()->orderBy('title')->pluck('title')->all())->toBe($expected);
})->with([
    ['2026-04-05', ['once', 'spring']],
    ['2026-03-19', []],
    ['2026-12-31', ['newyear']],
    ['2027-01-03', ['newyear']],
    ['2027-01-06', []],
    ['2026-08-14', ['oneday']],
]);

it('hides closed job posts from the public', function () {
    $open = JobPost::factory()->create(['is_active' => true, 'application_deadline' => now()->addWeek()]);
    $expired = JobPost::factory()->create(['is_active' => true, 'application_deadline' => now()->subDay()]);
    $inactive = JobPost::factory()->create(['is_active' => false, 'application_deadline' => null]);

    $this->get(route('careers.show', $open))->assertOk();
    $this->get(route('careers.show', $expired))->assertNotFound();
    $this->get(route('careers.show', $inactive))->assertNotFound();
});

it('shows a result only for a matching admission number', function () {
    $student = Student::factory()->create(['admission_number' => 'SR-1001']);
    $student->examinationResults()->create(['year' => '2026', 'detail' => 'Final', 'total_marks' => 100, 'obtained_marks' => 72]);

    $this->get('/examination-results?admission_number=SR-1001')->assertOk()->assertSee('fi-ta-table', escape: false);
    $this->get('/examination-results?admission_number=SR-9999')->assertOk()->assertDontSee('fi-ta-table', escape: false);
});

it('deletes a student together with their results', function () {
    $student = Student::factory()->create();
    $student->examinationResults()->create(['total_marks' => 100, 'obtained_marks' => 50]);

    $student->delete();

    expect(App\Models\ExaminationResult::count())->toBe(0);
});

it('strips scripts from rich content but keeps its formatting', function () {
    $html = '<h2>Heading</h2><p><strong>Bold</strong> <a href="https://example.org">link</a></p><ul><li>Item</li></ul>'
        .'<script>alert(1)</script><img src="x" onerror="alert(2)">';

    $announcement = Announcement::create(['title' => 'Notice', 'description' => 'd', 'content' => $html, 'is_recurring' => false, 'start_date' => now()]);
    $jobPost = JobPost::factory()->create(['content' => $html, 'is_active' => true, 'application_deadline' => null]);

    foreach ([route('announcement.show', $announcement), route('careers.show', $jobPost)] as $url) {
        $this->get($url)->assertOk()
            ->assertSee('<h2>Heading</h2>', false)
            ->assertSee('<a href="https://example.org">link</a>', false)
            ->assertSee('<li>Item</li>', false)
            ->assertDontSee('alert(1)', false)
            ->assertDontSee('onerror', false);
    }
});

it('renders every public page for a signed-in admin', function (string $page) {
    $this->actingAs(userWithRole(App\Enum\UserRole::SuperAdmin))->get($page)->assertOk();
})->with(['/', '/about', '/scholarships', '/gallery', '/contact', '/examination-results', '/careers']);
