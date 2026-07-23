<?php

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;
use Mockery\MockInterface;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    $this->withHeaders([
        'Accept' => 'application/json',
        'Origin' => 'http://localhost:3001',
        'Referer' => 'http://localhost:3001/login',
    ]);
});

test('spa can initialize csrf protection through the versioned endpoint', function () {
    $this->get('/api/v1/sanctum/csrf-cookie')
        ->assertNoContent()
        ->assertCookie('XSRF-TOKEN');
});

test('api allows credentialed cors requests from the configured frontend', function () {
    $this->call('OPTIONS', '/api/v1/auth/login', server: [
        'HTTP_ORIGIN' => 'http://localhost:3001',
        'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'POST',
    ])
        ->assertNoContent()
        ->assertHeader('Access-Control-Allow-Origin', 'http://localhost:3001')
        ->assertHeader('Access-Control-Allow-Credentials', 'true');
});

test('guest can register and receives the default user role', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'Nguyen Van A',
        'email' => 'new-user@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response
        ->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Registration successful')
        ->assertJsonPath('data.user.email', 'new-user@example.com')
        ->assertJsonPath('data.user.roles.0', 'user');

    $user = User::query()->where('email', 'new-user@example.com')->firstOrFail();

    expect($user->hasRole('user'))->toBeTrue();
    $this->assertAuthenticatedAs($user);
});

test('registration validation uses the api error envelope', function () {
    $this->postJson('/api/v1/auth/register', [])
        ->assertUnprocessable()
        ->assertJsonPath('success', false)
        ->assertJsonPath('message', 'Validation failed')
        ->assertJsonValidationErrors(['name', 'email', 'password']);
});

test('user can login read the current session and logout', function () {
    $role = Role::findOrCreate('admin', 'web');
    $permission = Permission::findOrCreate('demo', 'web');
    $role->givePermissionTo($permission);

    $user = User::factory()->create([
        'email' => 'admin@example.com',
        'password' => 'password123',
    ]);
    $user->assignRole($role);

    $this->postJson('/api/v1/auth/login', [
        'email' => 'ADMIN@example.com',
        'password' => 'password123',
        'remember' => true,
    ])
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.requires_two_factor', false)
        ->assertJsonPath('data.user.id', $user->id)
        ->assertJsonPath('data.user.roles.0', 'admin')
        ->assertJsonPath('data.user.permissions.0', 'demo');

    $this->getJson('/api/v1/auth/me')
        ->assertOk()
        ->assertJsonPath('data.user.email', 'admin@example.com');

    $this->postJson('/api/v1/auth/logout')
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Logout successful');

    $this->assertGuest('web');
});

test('legacy user without roles receives the default user role after login', function () {
    $user = User::factory()->create([
        'email' => 'legacy@example.com',
        'password' => 'password123',
    ]);

    expect($user->roles)->toBeEmpty();

    $this->postJson('/api/v1/auth/login', [
        'email' => 'legacy@example.com',
        'password' => 'password123',
    ])
        ->assertOk()
        ->assertJsonPath('data.user.roles.0', 'user');

    expect($user->fresh()->hasRole('user'))->toBeTrue();
});

test('invalid login credentials return validation errors', function () {
    User::factory()->create([
        'email' => 'member@example.com',
        'password' => 'correct-password',
    ]);

    $this->postJson('/api/v1/auth/login', [
        'email' => 'member@example.com',
        'password' => 'wrong-password',
    ])
        ->assertUnprocessable()
        ->assertJsonPath('success', false)
        ->assertJsonPath('message', 'Validation failed')
        ->assertJsonValidationErrors('email');
});

test('protected auth endpoint returns a standardized unauthenticated response', function () {
    $this->getJson('/api/v1/auth/me')
        ->assertUnauthorized()
        ->assertExactJson([
            'success' => false,
            'message' => 'Unauthenticated',
            'errors' => [],
        ]);
});

test('user can complete a two factor login challenge', function () {
    $user = User::factory()->withTwoFactor()->create([
        'email' => 'two-factor@example.com',
        'password' => 'password123',
    ]);

    $this->postJson('/api/v1/auth/login', [
        'email' => 'two-factor@example.com',
        'password' => 'password123',
    ])
        ->assertOk()
        ->assertJsonPath('data.requires_two_factor', true)
        ->assertJsonPath('data.user', null);

    $this->assertGuest();

    $this->mock(
        TwoFactorAuthenticationProvider::class,
        function (MockInterface $mock): void {
            $mock->shouldReceive('verify')
                ->once()
                ->with('secret', '123456')
                ->andReturnTrue();
        },
    );

    $this->postJson('/api/v1/auth/two-factor-challenge', [
        'code' => '123456',
    ])
        ->assertOk()
        ->assertJsonPath('data.requires_two_factor', false)
        ->assertJsonPath('data.user.id', $user->id);

    $this->assertAuthenticatedAs($user);
});

test('user can request and complete a password reset', function () {
    Notification::fake();
    config(['app.frontend_url' => 'http://localhost:3000']);

    $user = User::factory()->create([
        'email' => 'reset@example.com',
        'password' => 'old-password',
    ]);

    $this->postJson('/api/v1/auth/forgot-password', [
        'email' => $user->email,
    ])
        ->assertOk()
        ->assertJsonPath('success', true);

    Notification::assertSentTo(
        $user,
        ResetPasswordNotification::class,
        function (ResetPasswordNotification $notification) use ($user): bool {
            $resetUrl = $notification->toMail($user)->actionUrl;

            return str_starts_with(
                $resetUrl,
                'http://localhost:3000/reset-password?token=',
            );
        },
    );

    $token = Password::broker()->createToken($user);

    $this->postJson('/api/v1/auth/reset-password', [
        'token' => $token,
        'email' => $user->email,
        'password' => 'new-password123',
        'password_confirmation' => 'new-password123',
    ])
        ->assertOk()
        ->assertJsonPath('success', true);

    expect(Hash::check('new-password123', $user->fresh()->password))->toBeTrue();
});
