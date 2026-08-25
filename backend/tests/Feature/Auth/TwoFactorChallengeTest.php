<?php

use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Event;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;
use Laravel\Fortify\Events\TwoFactorAuthenticationFailed;
use Laravel\Fortify\Events\ValidTwoFactorAuthenticationCodeProvided;
use Tests\Support\TestData;
use Tests\Support\TestResponseAssertions;

use function Pest\Laravel\assertAuthenticatedAs;
use function Pest\Laravel\assertGuest;

test('two-factor challenge follows the shared test data contract', function (array $case) {
    Event::fake([
        Login::class,
        TwoFactorAuthenticationFailed::class,
        ValidTwoFactorAuthenticationCodeProvided::class,
    ]);

    $user = null;
    $aliases = [
        'two_factor' => [
            'code' => '123456',
            'recovery_code' => 'recovery-code',
            'used_recovery_code' => 'used-recovery-code',
        ],
    ];

    if (in_array($case['actor'], ['two_factor_user', 'two_factor_user_without_secret'], true)) {
        $recoveryCodes = in_array('used_recovery_code', $case['preconditions'], true)
            ? ['unused-recovery-code']
            : ['recovery-code'];

        $user = regularUser();
        $user->forceFill([
            'two_factor_secret' => $case['actor'] === 'two_factor_user_without_secret'
                ? null
                : encrypt('two-factor-secret'),
            'two_factor_recovery_codes' => encrypt(json_encode($recoveryCodes, JSON_THROW_ON_ERROR)),
            'two_factor_confirmed_at' => now(),
        ])->save();

        $this->withSession(['login.id' => $user->id]);
    }

    if (in_array($case['case_id'], [
        'AUTH-2FA-VERIFY-EP-001',
        'AUTH-2FA-VERIFY-EP-002',
    ], true)) {
        $provider = mock(TwoFactorAuthenticationProvider::class);
        $provider->shouldReceive('verify')
            ->once()
            ->with('two-factor-secret', $case['case_id'] === 'AUTH-2FA-VERIFY-EP-001' ? '123456' : '000000')
            ->andReturn($case['case_id'] === 'AUTH-2FA-VERIFY-EP-001');
        app()->instance(TwoFactorAuthenticationProvider::class, $provider);
    }

    $case = TestData::resolveAliases($case, $aliases);
    $request = $case['request'];
    $response = $this->withHeaders($request['headers'])
        ->postJson($request['endpoint'], $request['body']);

    TestResponseAssertions::assertForCase($response, $case);

    if ($case['expected']['status'] === 200) {
        assertAuthenticatedAs($user);
        $response->assertJsonPath('data.user.id', $user?->id);
        Event::assertDispatched(ValidTwoFactorAuthenticationCodeProvided::class);
    } else {
        assertGuest();
    }

    if ($case['case_id'] === 'AUTH-2FA-VERIFY-EP-003') {
        expect($user?->fresh()->recoveryCodes())->not->toContain('recovery-code');
    }

    if (in_array($case['case_id'], [
        'AUTH-2FA-VERIFY-EP-002',
        'AUTH-2FA-VERIFY-EP-004',
    ], true)) {
        Event::assertDispatched(TwoFactorAuthenticationFailed::class);
    }
})->with(TestData::load('auth/two-factor-challenge.json'));
