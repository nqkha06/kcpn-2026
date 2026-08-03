<?php

use App\Models\Budget;
use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    $this->withHeaders([
        'Accept' => 'application/json',
        'Origin' => 'http://localhost:3000',
        'Referer' => 'http://localhost:3000/admin',
    ]);
});

test('guests cannot delete a budget', function () {
    $budget = Budget::factory()->create();

    $this->deleteJson('/api/v1/admin/budgets/'.$budget->id)
        ->assertUnauthorized()
        ->assertJsonPath('message', 'Unauthenticated');

    $this->assertDatabaseHas('budgets', ['id' => $budget->id]);
});

test('non admin users cannot delete a budget', function () {
    $user = User::factory()->create();
    $user->assignRole(Role::findOrCreate('user', 'web'));
    $budget = Budget::factory()->create();

    $this->actingAs($user, 'web')
        ->deleteJson('/api/v1/admin/budgets/'.$budget->id)
        ->assertForbidden()
        ->assertJsonPath('message', 'Forbidden');

    $this->assertDatabaseHas('budgets', ['id' => $budget->id]);
});

test('admin can delete a budget', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::findOrCreate('admin', 'web'));

    $budget = Budget::factory()->create();

    $this->actingAs($admin, 'web')
        ->deleteJson('/api/v1/admin/budgets/'.$budget->id)
        ->assertOk()
        ->assertJsonPath('message', 'Budget deleted successfully');

    $this->assertDatabaseMissing('budgets', ['id' => $budget->id]);
});

test('deleting a non existent budget returns not found', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::findOrCreate('admin', 'web'));

    $this->actingAs($admin, 'web')
        ->deleteJson('/api/v1/admin/budgets/999999')
        ->assertNotFound();
});