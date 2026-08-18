<?php

use App\Models\User;
use Tests\Support\TestData;
use Tests\Support\TestResponseAssertions;

function sharedBoundaryEmail(int $length): string
{
    return str_repeat('a', 64).'@'
        .str_repeat('b', 61).'.'
        .str_repeat('c', 61).'.'
        .str_repeat('d', $length - 193).'.com';
}

function executeUserSettingsDataCase(object $testCase, array $case): void
{
    $user = regularUser();
    $existingUser = User::factory()->create(['email' => 'shared-existing-profile@example.test']);
    $verifiedAt = $user->email_verified_at;

    if (in_array('currency_already_usd', $case['preconditions'], true)) {
        $user->setMeta('currency', 'USD');
    }

    $case = TestData::resolveAliases($case, [
        'user' => ['id' => $user->id, 'email' => $user->email],
        'existing_user' => ['email' => $existingUser->email],
        'email_254' => ['value' => sharedBoundaryEmail(254)],
        'email_255' => ['value' => sharedBoundaryEmail(255)],
        'email_256' => ['value' => sharedBoundaryEmail(256)],
    ]);

    if ($case['actor'] === 'user') {
        $testCase->actingAs($user);
    } elseif ($case['actor'] === 'admin') {
        $testCase->actingAs(adminUser());
    }

    $response = $testCase->json(
        $case['request']['method'],
        $case['request']['endpoint'],
        $case['request']['body'],
        $case['request']['headers'],
    );
    TestResponseAssertions::assertForCase($response, $case);

    if ($case['expected']['database_change']['operation'] === 'update') {
        $user->refresh();

        if (str_ends_with($case['request']['endpoint'], '/profile')) {
            expect($user->name)->toBe($case['request']['body']['name'])
                ->and($user->email)->toBe($case['request']['body']['email']);

            if ($case['case_id'] === 'USR-SET-PROFILE-UPDATE-BUS-014') {
                expect($user->email_verified_at?->equalTo($verifiedAt))->toBeTrue();
            }
        } else {
            expect($user->getMeta('currency'))->toBe($case['request']['body']['currency']);
        }
    }
}

test('user profile settings follow shared execution data', function (array $case): void {
    executeUserSettingsDataCase($this, $case);
})->with(TestData::load('user/settings/profile.json'));

test('user preference settings follow shared execution data', function (array $case): void {
    executeUserSettingsDataCase($this, $case);
})->with(TestData::load('user/settings/preferences.json'));
