<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;

use function Pest\Laravel\assertAuthenticatedAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\postJson;

test('a guest can register through the API', function () {
    postJson('/api/v1/auth/register', [
        'name' => 'API User',
        'email' => 'api-user@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])
        ->assertCreated()
        ->assertJsonPath('data.user.email', 'api-user@example.com')
        ->assertJsonPath('data.user.roles.0', 'user')
        ->assertJsonMissingPath('data.user.password')
        ->assertJsonMissingPath('data.user.remember_token');

    assertDatabaseHas('users', ['email' => 'api-user@example.com']);

    $user = User::query()->where('email', 'api-user@example.com')->firstOrFail();

    expect(Hash::check('password', $user->password))->toBeTrue();
    assertAuthenticatedAs($user);
});

test('registration validates required fields', function () {
    postJson('/api/v1/auth/register', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name', 'email', 'password', 'password_confirmation']);
});

test('registration rejects an email that is already registered', function () {
    User::factory()->create(['email' => 'existing@example.com']);

    postJson('/api/v1/auth/register', [
        'name' => 'Existing User',
        'email' => 'existing@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('email');

    expect(User::query()->where('email', 'existing@example.com')->count())->toBe(1);
});

test('registration rejects a mismatched password confirmation', function () {
    postJson('/api/v1/auth/register', [
        'name' => 'API User',
        'email' => 'mismatch@example.com',
        'password' => 'password',
        'password_confirmation' => 'different-password',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('password');

    assertDatabaseMissing('users', ['email' => 'mismatch@example.com']);
});

test('registration dispatches the registered event', function () {
    Event::fake([Registered::class]);

    postJson('/api/v1/auth/register', [
        'name' => 'Event User',
        'email' => 'event@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertCreated();

    Event::assertDispatched(
        Registered::class,
        fn (Registered $event): bool => $event->user->email === 'event@example.com',
    );
});
