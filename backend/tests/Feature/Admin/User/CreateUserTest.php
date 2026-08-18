<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\Support\TestData;
use Tests\Support\TestResponseAssertions;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\postJson;

test('an admin can create a user', function () {
    actingAs(adminUser())
        ->postJson('/api/v1/admin/users', [
            'name' => 'API Managed User',
            'email' => 'managed@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'roles' => [],
        ])
        ->assertCreated()
        ->assertJsonPath('data.email', 'managed@example.com');

    assertDatabaseHas('users', ['email' => 'managed@example.com']);
});

test('a guest cannot create a user', function () {
    postJson('/api/v1/admin/users', [
        'name' => 'Guest Managed User',
        'email' => 'guest-managed@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ])->assertUnauthorized();

    assertDatabaseMissing('users', ['email' => 'guest-managed@example.com']);
});

test('a regular user cannot create a user', function () {
    actingAs(regularUser())
        ->postJson('/api/v1/admin/users', [
            'name' => 'Unauthorized Managed User',
            'email' => 'unauthorized-managed@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])
        ->assertForbidden();

    assertDatabaseMissing('users', ['email' => 'unauthorized-managed@example.com']);
});

test('user creation validates required fields', function () {
    actingAs(adminUser())
        ->postJson('/api/v1/admin/users', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name', 'email', 'password']);
});

test('user creation rejects an existing email', function () {
    User::factory()->create(['email' => 'existing@example.com']);

    actingAs(adminUser())
        ->postJson('/api/v1/admin/users', [
            'name' => 'Existing User',
            'email' => 'existing@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('email');

    expect(User::query()->where('email', 'existing@example.com')->count())->toBe(1);
});

test('user creation rejects invalid and duplicate role ids', function () {
    $role = Role::findOrCreate('manager', 'web');

    actingAs(adminUser())
        ->postJson('/api/v1/admin/users', [
            'name' => 'Role User',
            'email' => 'role-user@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'roles' => [$role->id, $role->id, 999999],
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['roles.1', 'roles.2']);

    assertDatabaseMissing('users', ['email' => 'role-user@example.com']);
});

test('an admin can create a user with roles and a hashed password', function () {
    $role = Role::findOrCreate('manager', 'web');

    actingAs(adminUser())
        ->postJson('/api/v1/admin/users', [
            'name' => 'Managed Role User',
            'email' => 'managed-role@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'roles' => [$role->id],
        ])
        ->assertCreated()
        ->assertJsonPath('data.roles.0.name', 'manager')
        ->assertJsonMissingPath('data.password');

    $user = User::query()->where('email', 'managed-role@example.com')->firstOrFail();

    expect($user->hasRole('manager'))->toBeTrue()
        ->and(Hash::check('password123', $user->password))->toBeTrue();
});

test('admin user create follows shared execution data', function (array $case) {
    $role = Role::findOrCreate('data-manager', 'web');
    $existingUser = User::factory()->create(['email' => 'existing-data-user@example.test']);
    $emailBoundary = static function (int $length): string {
        return str_repeat('a', 64).'@'
            .str_repeat('b', 61).'.'
            .str_repeat('c', 61).'.'
            .str_repeat('d', $length - 193).'.com';
    };

    $case = TestData::resolveAliases($case, [
        'role' => ['id' => $role->id],
        'existing_user' => ['email' => $existingUser->email],
        'new_user' => [
            'password' => 'ValidPassword123!',
            'other_password' => 'DifferentPassword123!',
        ],
        'email_254' => ['value' => $emailBoundary(254)],
        'email_255' => ['value' => $emailBoundary(255)],
        'email_256' => ['value' => $emailBoundary(256)],
        'missing' => ['id' => 999_999_999],
    ]);

    if ($case['actor'] === 'admin') {
        $this->actingAs(adminUser());
    } elseif ($case['actor'] === 'user') {
        $this->actingAs(regularUser());
    }

    $beforeCount = User::query()->count();
    $response = $this->json(
        $case['request']['method'],
        $case['request']['endpoint'],
        $case['request']['body'],
        $case['request']['headers'],
    );

    TestResponseAssertions::assertForCase($response, $case);

    $expectedDelta = $case['expected']['database_change']['operation'] === 'insert' ? 1 : 0;
    expect(User::query()->count())->toBe($beforeCount + $expectedDelta);

    if ($expectedDelta === 1) {
        $createdUser = User::query()->with('roles')->findOrFail($response->json('data.id'));

        expect($createdUser->hasRole($role))->toBeTrue()
            ->and(Hash::check($case['request']['body']['password'], $createdUser->password))->toBeTrue();
    }
})->with(TestData::load('admin/users/create.json'));
