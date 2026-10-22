<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

it('sets a new password and e-mail from the terminal', function () {
    $user = User::factory()->create(['email' => 'test@example.com']);

    $this->artisan('srafes:reset-password', ['email' => 'test@example.com', '--new-email' => 'admin@srafes.example'])
        ->expectsQuestion('New password for test@example.com', 'a-strong-password')
        ->expectsQuestion('Type it again', 'a-strong-password')
        ->assertSuccessful();

    $user->refresh();
    expect($user->email)->toBe('admin@srafes.example')
        ->and(Hash::check('a-strong-password', $user->password))->toBeTrue();
});

it('changes nothing when the confirmation does not match', function () {
    $user = User::factory()->create(['email' => 'test@example.com']);
    $originalHash = $user->password;

    $this->artisan('srafes:reset-password', ['email' => 'test@example.com'])
        ->expectsQuestion('New password for test@example.com', 'a-strong-password')
        ->expectsQuestion('Type it again', 'something-else-1')
        ->assertFailed();

    expect($user->fresh()->password)->toBe($originalHash);
});

it('rejects an e-mail that belongs to another account', function () {
    User::factory()->create(['email' => 'test@example.com']);
    User::factory()->create(['email' => 'taken@example.org']);

    $this->artisan('srafes:reset-password', ['email' => 'test@example.com', '--new-email' => 'taken@example.org'])
        ->assertFailed();
});
