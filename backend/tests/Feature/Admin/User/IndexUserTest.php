<?php

use App\Models\User;
use Spatie\Permission\Models\Role;
use Tests\Support\TestData;
use Tests\Support\TestResponseAssertions;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;

test('an admin can list users', function () {
    $admin = adminUser();
    User::factory()->create(['email' => 'listed@example.com']);

    actingAs($admin)
        ->getJson('/api/v1/admin/users')
        ->assertOk()
        ->assertJsonPath('meta.total', 2)
        ->assertJsonFragment(['email' => 'listed@example.com']);
});

test('a guest cannot list users', function () {
    getJson('/api/v1/admin/users')->assertUnauthorized();
});

test('a regular user cannot list users', function () {
    actingAs(regularUser())
        ->getJson('/api/v1/admin/users')
        ->assertForbidden();
});

test('an admin can search users by name or email', function () {
    $admin = adminUser();
    $user = User::factory()->create([
        'name' => 'Search Target',
        'email' => 'target@example.com',
    ]);
    User::factory()->create(['name' => 'Hidden Result']);

    foreach (['Search Target', 'target@example.com'] as $search) {
        actingAs($admin)
            ->getJson('/api/v1/admin/users?search='.urlencode($search))
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.id', $user->id);
    }
});

test('an admin can search users by id', function () {
    $admin = adminUser();
    $user = User::factory()->create();

    actingAs($admin)
        ->getJson('/api/v1/admin/users?search='.$user->id)
        ->assertOk()
        ->assertJsonPath('meta.total', 1)
        ->assertJsonPath('data.0.id', $user->id);
})->todo('AdminUserService calls the undefined Eloquent Builder method orWhereKey');

test('an admin can filter users by role and creation date', function () {
    $admin = adminUser();
    $role = Role::findOrCreate('manager', 'web');
    $matching = User::factory()->create(['created_at' => '2026-07-15 10:00:00']);
    $matching->assignRole($role);
    User::factory()->create(['created_at' => '2026-06-15 10:00:00']);

    actingAs($admin)
        ->getJson('/api/v1/admin/users?role=manager&created_from=2026-07-01&created_to=2026-07-31')
        ->assertOk()
        ->assertJsonPath('meta.total', 1)
        ->assertJsonPath('data.0.id', $matching->id);
});

test('an admin can sort and paginate users', function () {
    $admin = adminUser();
    User::factory()->create(['name' => 'Zulu']);
    $alpha = User::factory()->create(['name' => 'Alpha']);

    actingAs($admin)
        ->getJson('/api/v1/admin/users?sort=name&direction=asc&per_page=2')
        ->assertOk()
        ->assertJsonPath('data.0.id', $alpha->id)
        ->assertJsonPath('meta.per_page', 2)
        ->assertJsonPath('meta.total', 3);
});

test('user list query parameters are validated', function () {
    actingAs(adminUser())
        ->getJson('/api/v1/admin/users?role=missing-role&created_from=invalid&sort=password&direction=sideways&per_page=101')
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['role', 'created_from', 'sort', 'direction', 'per_page']);
});

test('admin user index follows shared execution data', function (array $case) {
    $role = Role::findOrCreate('data-manager', 'web');
    $listedUser = User::factory()->create();
    $listedUser->assignRole($role);

    $case = TestData::resolveAliases($case, ['role' => ['name' => $role->name]]);

    if ($case['actor'] === 'admin') {
        $this->actingAs(adminUser());
    } elseif ($case['actor'] === 'user') {
        $this->actingAs(regularUser());
    }

    $query = http_build_query($case['request']['query']);
    $endpoint = $case['request']['endpoint'].($query === '' ? '' : '?'.$query);
    $beforeCount = User::query()->count();
    $response = $this->json('GET', $endpoint, [], $case['request']['headers']);

    TestResponseAssertions::assertForCase($response, $case);
    expect(User::query()->count())->toBe($beforeCount);
})->with(TestData::load('admin/users/index.json'));
