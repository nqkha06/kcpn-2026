<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Tests\Support\TestData;
use Tests\Support\TestResponseAssertions;

use function Pest\Laravel\assertAuthenticatedAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\postJson;

test('registration follows the shared boundary and partition data', function (array $case) {
    Event::fake([Registered::class]);

    $existingUser = null;

    if (in_array('email_already_registered', $case['preconditions'], true)) {
        $existingUser = User::factory()->create(['email' => 'existing@example.com']);
    }

    $emailLocalPart = str_repeat('a', 64);
    $emailDomainPrefix = str_repeat('b', 60).'.'.str_repeat('c', 60).'.'.str_repeat('d', 60).'.';
    $aliases = [
        'register' => [
            'email254' => $emailLocalPart.'@'.$emailDomainPrefix.str_repeat('e', 6),
            'email255' => $emailLocalPart.'@'.$emailDomainPrefix.str_repeat('e', 7),
            'email256' => $emailLocalPart.'@'.$emailDomainPrefix.str_repeat('e', 8),
        ],
        'existing_user' => ['email' => $existingUser?->email],
    ];

    $case = TestData::resolveAliases($case, $aliases);
    $request = $case['request'];
    $response = postJson($request['endpoint'], $request['body'], $request['headers']);

    TestResponseAssertions::assertForCase($response, $case);

    $email = $request['body']['email'] ?? null;

    if ($case['expected']['status'] === 201) {
        assertDatabaseHas('users', ['email' => $email]);

        $user = User::query()->where('email', $email)->firstOrFail();
        $password = $request['body']['password'];

        expect(is_string($password) && Hash::check($password, $user->password))->toBeTrue();
        assertAuthenticatedAs($user);
        Event::assertDispatched(
            Registered::class,
            fn (Registered $event): bool => $event->user->is($user),
        );
    } elseif (is_string($email) && $existingUser === null) {
        assertDatabaseMissing('users', ['email' => $email]);
        Event::assertNotDispatched(Registered::class);
    }
})->with(TestData::load('auth/register.json'));
