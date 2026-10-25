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
    '/admin/academic-sessions',
    '/admin/academic-sessions/create',
    '/admin/academic-classes/create',
    '/admin/subjects',
    '/admin/subjects/create',
    '/admin/import-students',
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

it('never lets a bulk delete remove your own account', function () {
    $admin = userWithRole(UserRole::SuperAdmin);
    $other = userWithRole(UserRole::Staff);

    Livewire\Livewire::actingAs($admin)
        ->test(App\Filament\Resources\Users\Pages\ListUsers::class)
        ->selectTableRecords([$admin->getKey(), $other->getKey()])
        ->callAction(Filament\Actions\Testing\TestAction::make('delete')->table()->bulk());

    expect(App\Models\User::find($admin->id))->not->toBeNull()
        ->and(App\Models\User::find($other->id))->toBeNull();
});

it('ignores crafted requests that change your own roles or active flag', function () {
    $admin = userWithRole(UserRole::SuperAdmin);

    Livewire\Livewire::actingAs($admin)
        ->test(App\Filament\Resources\Users\Pages\EditUser::class, ['record' => $admin->getKey()])
        ->set('data.is_active', false)
        ->set('data.roles', [])
        ->call('save');

    $admin->refresh();
    expect($admin->is_active)->toBeTrue()
        ->and($admin->hasRole(UserRole::SuperAdmin->value))->toBeTrue();
});

it('guards site settings by permission, not just panel access', function () {
    // A role with panel access but no site content permission, like one a future phase might add
    $user = App\Models\User::factory()->create(['is_active' => true]);
    $user->assignRole(Role::create(['name' => 'panel_only', 'guard_name' => 'web'])->givePermissionTo(Permission::AccessAdminPanel->value));

    $this->actingAs($user)->get('/admin/site-settings')->assertForbidden();

    $this->actingAs(userWithRole(UserRole::Staff))->get('/admin/site-settings')->assertOk();
});

it('lets only careers managers preview closed job posts', function () {
    $closed = App\Models\JobPost::factory()->create(['is_active' => false]);

    $this->actingAs(userWithRole(UserRole::Teacher))->get(route('careers.show', $closed))->assertNotFound();
    $this->actingAs(userWithRole(UserRole::Staff, active: false))->get(route('careers.show', $closed))->assertNotFound();
    $this->actingAs(userWithRole(UserRole::Staff))->get(route('careers.show', $closed))->assertOk();
});
