<?php

use App\Authorization\RoleRegistry;
use App\Enum\Permission;
use App\Enum\UserRole;
use App\Models\Student;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Role;

dataset('admin pages', [
    '/admin',
    '/admin/students',
    '/admin/students/create',
    '/admin/employees',
    '/admin/academic-classes',
    '/admin/announcements',
    '/admin/examination-results',
    '/admin/job-posts',
    '/admin/media-assets',
    '/admin/users',
    '/admin/site-settings',
]);

it('creates every role with exactly the permissions in the registry', function () {
    foreach (UserRole::cases() as $role) {
        $expected = collect(RoleRegistry::permissionsFor($role))->map->value->sort()->values();

        expect(Role::findByName($role->value)->permissions->pluck('name')->sort()->values())->toEqual($expected);
    }
});

it('sends guests to the login page', function () {
    $this->get('/admin')->assertRedirect('/admin/login');
});

it('blocks users without a role from the admin panel', function () {
    $this->actingAs(userWithRole(null))->get('/admin')->assertForbidden();
});

it('blocks teachers, students and parents from the admin panel', function (UserRole $role) {
    $this->actingAs(userWithRole($role))->get('/admin')->assertForbidden();
})->with([UserRole::Teacher, UserRole::Student, UserRole::Parent]);

it('blocks deactivated super admins', function () {
    $this->actingAs(userWithRole(UserRole::SuperAdmin, active: false))->get('/admin')->assertForbidden();
});

it('lets a super admin open every admin page', function (string $page) {
    $this->actingAs(userWithRole(UserRole::SuperAdmin))->get($page)->assertOk();
})->with('admin pages');

it('lets a super admin open a student edit page', function () {
    $student = Student::factory()->create();

    $this->actingAs(userWithRole(UserRole::SuperAdmin))->get("/admin/students/{$student->id}/edit")->assertOk();
});

it('limits staff to their permissions', function () {
    $staff = userWithRole(UserRole::Staff);

    $this->actingAs($staff)->get('/admin/students')->assertOk();
    $this->actingAs($staff)->get('/admin/announcements')->assertOk();
    $this->actingAs($staff)->get('/admin/examination-results')->assertOk();

    $this->actingAs($staff)->get('/admin/users')->assertForbidden();
    $this->actingAs($staff)->get('/admin/examination-results/create')->assertForbidden();
    $this->actingAs($staff)->get('/admin/employees/create')->assertForbidden();
});

it('gives principals everything except user management', function () {
    $principal = userWithRole(UserRole::Principal);

    $this->actingAs($principal)->get('/admin/examination-results/create')->assertOk();
    $this->actingAs($principal)->get('/admin/employees/create')->assertOk();
    $this->actingAs($principal)->get('/admin/users')->assertForbidden();
});

it('never lets users delete their own account', function () {
    $admin = userWithRole(UserRole::SuperAdmin);

    expect($admin->can('delete', $admin))->toBeFalse()
        ->and($admin->can('delete', userWithRole(UserRole::Staff)))->toBeTrue();
});

it('denies every ability to inactive users even with a role', function () {
    $user = userWithRole(UserRole::Principal, active: false);

    expect($user->can('viewAny', Student::class))->toBeFalse();
});

it('restricts the translation manager to site content managers', function () {
    expect(Gate::allows('use-translation-manager'))->toBeFalse()
        ->and(Gate::forUser(userWithRole(UserRole::Staff))->allows('use-translation-manager'))->toBeTrue()
        ->and(Gate::forUser(userWithRole(UserRole::Teacher))->allows('use-translation-manager'))->toBeFalse();
});

it('defines no fee or payment permissions', function () {
    $names = collect(Permission::cases())->map->value;

    expect($names->filter(fn (string $name) => preg_match('/fee|payment|dues|voucher/', $name)))->toBeEmpty();
});
