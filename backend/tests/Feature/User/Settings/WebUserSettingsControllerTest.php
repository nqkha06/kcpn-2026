<?php

use function Pest\Laravel\actingAs;

test('web user can view user settings page with profile and preferences', function () {
    $user = regularUser();
    $user->setMeta('currency', 'EUR');

    actingAs($user, 'web')
        ->get(route('user.settings'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('User/Setting')
            ->where('userProfile.name', $user->name)
            ->where('userProfile.email', $user->email)
            ->where('preferences.currency', 'EUR')
            ->has('currencyOptions')
        );
});

test('web user can update profile via web request and resets email verification on change', function () {
    $user = regularUser();

    actingAs($user, 'web')
        ->patch(route('user.settings.profile.update'), [
            'name' => 'Updated Web Name',
            'email' => 'new-web-email@example.com',
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    $freshUser = $user->fresh();
    expect($freshUser->name)->toBe('Updated Web Name')
        ->and($freshUser->email)->toBe('new-web-email@example.com')
        ->and($freshUser->email_verified_at)->toBeNull();
});

test('web user update profile preserves verification when email is unchanged', function () {
    $user = regularUser();
    $verifiedAt = $user->email_verified_at;

    actingAs($user, 'web')
        ->patch(route('user.settings.profile.update'), [
            'name' => 'Another Web Name',
            'email' => $user->email,
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($user->fresh()->email_verified_at?->equalTo($verifiedAt))->toBeTrue();
});

test('web user can update currency preferences via web request', function () {
    $user = regularUser();

    actingAs($user, 'web')
        ->patch(route('user.settings.preferences.update'), [
            'currency' => 'GBP',
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($user->fresh()->getMeta('currency'))->toBe('GBP');
});
