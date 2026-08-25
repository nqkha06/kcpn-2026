<?php

use App\Models\Category;
use App\Models\UserWallet;
use Tests\Support\TestData;
use Tests\Support\TestResponseAssertions;

use function Pest\Laravel\actingAs;

test('admin transaction options follow the shared test data contract', function (array $case) {
    $user = null;
    $wallet = null;
    $category = null;

    if (in_array('user_wallet_and_category_exist', $case['preconditions'], true)) {
        $user = regularUser();
        $wallet = UserWallet::factory()->for($user)->create();
        $category = Category::factory()->create();
    }

    if ($case['actor'] === 'admin') {
        actingAs(adminUser());
    } elseif ($case['actor'] === 'user') {
        actingAs(regularUser());
    }

    $request = $case['request'];
    $response = $this->getJson($request['endpoint'], $request['headers']);

    TestResponseAssertions::assertForCase($response, $case);

    if ($case['actor'] === 'admin') {
        $response
            ->assertJsonFragment(['id' => $user?->id, 'name' => $user?->name, 'email' => $user?->email])
            ->assertJsonFragment(['id' => $wallet?->id, 'user_id' => $user?->id, 'name' => $wallet?->name])
            ->assertJsonFragment(['id' => $category?->id, 'name' => $category?->name]);
    }
})->with(TestData::load('admin/transactions/options.json'));
