<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| Feature tests run against a fresh in-memory database (see phpunit.xml and
| Tests\TestCase, which aborts if the real database would be used).
|
*/

pest()->extend(Tests\TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

function userWithRole(?App\Enum\UserRole $role, bool $active = true): App\Models\User
{
    $user = App\Models\User::factory()->create(['is_active' => $active]);

    if ($role) {
        $user->assignRole($role->value);
    }

    return $user;
}
