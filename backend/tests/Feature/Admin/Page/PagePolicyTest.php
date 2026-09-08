<?php

use App\Models\Page;
use App\Policies\PagePolicy;

test('page policy methods allow admin and reject regular user', function () {
    $policy = new PagePolicy;
    $admin = adminUser();
    $user = regularUser();
    $page = Page::query()->create([
        'user_id' => $admin->id,
        'title' => 'Policy Test Page',
        'slug' => 'policy-test-page',
        'status' => 'published',
    ]);

    expect($policy->viewAny($admin))->toBeTrue()
        ->and($policy->viewAny($user))->toBeFalse()
        ->and($policy->view($admin, $page))->toBeTrue()
        ->and($policy->view($user, $page))->toBeFalse()
        ->and($policy->create($admin))->toBeTrue()
        ->and($policy->create($user))->toBeFalse()
        ->and($policy->update($admin, $page))->toBeTrue()
        ->and($policy->update($user, $page))->toBeFalse()
        ->and($policy->delete($admin, $page))->toBeTrue()
        ->and($policy->delete($user, $page))->toBeFalse()
        ->and($policy->restore($admin, $page))->toBeTrue()
        ->and($policy->restore($user, $page))->toBeFalse()
        ->and($policy->forceDelete($admin, $page))->toBeTrue()
        ->and($policy->forceDelete($user, $page))->toBeFalse();
});
