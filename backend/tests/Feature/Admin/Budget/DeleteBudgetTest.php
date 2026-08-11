<?php

use App\Models\Budget;
use App\Models\User;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\deleteJson;
use function Pest\Laravel\withHeaders;

beforeEach(function (): void {
    withHeaders([
        'Accept' => 'application/json',
        'Origin' => 'http://localhost:3000',
        'Referer' => 'http://localhost:3000/admin',
    ]);
});

test('guests cannot delete a budget', function () {
    $budget = Budget::factory()->create();

    deleteJson('/api/v1/admin/budgets/'.$budget->id)
        ->assertUnauthorized()
        ->assertJsonPath('message', 'Unauthenticated');

    assertDatabaseHas('budgets', ['id' => $budget->id]);
});

test('non admin users cannot delete a budget', function () {
    $user = User::factory()->create();
    $user->assignRole(Role::findOrCreate('user', 'web'));
    $budget = Budget::factory()->create();

    actingAs($user, 'web')
        ->deleteJson('/api/v1/admin/budgets/'.$budget->id)
        ->assertForbidden()
        ->assertJsonPath('message', 'Forbidden');

    assertDatabaseHas('budgets', ['id' => $budget->id]);
});

test('admin can delete a budget', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::findOrCreate('admin', 'web'));

    $budget = Budget::factory()->create();

    actingAs($admin, 'web')
        ->deleteJson('/api/v1/admin/budgets/'.$budget->id)
        ->assertOk()
        ->assertJsonPath('message', 'Budget deleted successfully');

    assertDatabaseMissing('budgets', ['id' => $budget->id]);
});

test('deleting a non existent budget returns not found', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::findOrCreate('admin', 'web'));

    actingAs($admin, 'web')
        ->deleteJson('/api/v1/admin/budgets/999999')
        ->assertNotFound();
});
