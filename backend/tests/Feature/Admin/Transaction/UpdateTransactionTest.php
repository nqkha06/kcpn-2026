<?php

use App\Models\Category;
use App\Models\ExpenseTransaction;
use App\Models\UserWallet;
use Tests\Support\TestData;
use Tests\Support\TestResponseAssertions;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\putJson;

test('an admin can update a transaction', function () {
    $transaction = ExpenseTransaction::factory()->create(['status' => 'posted']);

    actingAs(adminUser())
        ->putJson("/api/v1/admin/transactions/{$transaction->id}", [
            'user_id' => $transaction->user_id,
            'wallet_id' => $transaction->wallet_id,
            'category_id' => $transaction->category_id,
            'type' => 'expense',
            'amount' => 180000,
            'transacted_at' => '2026-07-30',
            'status' => 'pending',
            'note' => 'Admin updated',
            'labels' => ['updated'],
        ])
        ->assertOk()
        ->assertJsonPath('data.amount', 180000)
        ->assertJsonPath('data.status', 'pending');

    assertDatabaseHas('expense_transactions', ['id' => $transaction->id, 'status' => 'pending']);
});

test('a guest cannot update an admin transaction', function () {
    $transaction = ExpenseTransaction::factory()->create();

    putJson("/api/v1/admin/transactions/{$transaction->id}", [])
        ->assertUnauthorized();
});

test('a regular user cannot update a transaction through the admin endpoint', function () {
    $transaction = ExpenseTransaction::factory()->forUser(regularUser())->create();

    actingAs($transaction->user)
        ->putJson("/api/v1/admin/transactions/{$transaction->id}", [])
        ->assertForbidden();
});

test('admin transaction update validates required fields', function () {
    $transaction = ExpenseTransaction::factory()->create();

    actingAs(adminUser())
        ->putJson("/api/v1/admin/transactions/{$transaction->id}", [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'user_id',
            'wallet_id',
            'type',
            'amount',
            'transacted_at',
            'status',
        ]);
});

test('admin transaction update enforces the selected users wallet ownership', function () {
    $transaction = ExpenseTransaction::factory()->create();
    $selectedUser = regularUser();
    $otherWallet = UserWallet::factory()->for(regularUser())->create();

    actingAs(adminUser())
        ->putJson("/api/v1/admin/transactions/{$transaction->id}", [
            'user_id' => $selectedUser->id,
            'wallet_id' => $otherWallet->id,
            'type' => 'expense',
            'amount' => 100,
            'transacted_at' => '2026-08-01',
            'status' => 'posted',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('wallet_id');

    expect($transaction->fresh()->user_id)->not->toBe($selectedUser->id);
});

test('updating a missing admin transaction returns not found', function () {
    $user = regularUser();
    $wallet = UserWallet::factory()->for($user)->create();

    actingAs(adminUser())
        ->putJson('/api/v1/admin/transactions/999999', [
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'type' => 'expense',
            'amount' => 100,
            'transacted_at' => '2026-08-01',
            'status' => 'posted',
        ])
        ->assertNotFound();
});

test('admin transaction update follows shared execution data', function (array $case) {
    $user = regularUser();
    $wallet = UserWallet::factory()->for($user)->create(['opening_balance' => 100]);
    $category = Category::factory()->create(['status' => 'active']);
    $transaction = ExpenseTransaction::factory()->forUser($user)->expense()->posted()->create([
        'wallet_id' => $wallet->id,
        'category_id' => $category->id,
        'amount' => 25,
    ]);
    $otherUser = regularUser();
    $otherWallet = UserWallet::factory()->for($otherUser)->create();
    $otherCategory = Category::factory()->create(['user_id' => $otherUser->id]);
    $isMissingTransaction = in_array('missing_transaction_alias', $case['preconditions'], true);

    $case = TestData::resolveAliases($case, [
        'user' => ['id' => $user->id],
        'wallet' => ['id' => $wallet->id],
        'category' => ['id' => $category->id],
        'transaction' => ['id' => $isMissingTransaction ? 999_999_999 : $transaction->id],
        'other_wallet' => ['id' => $otherWallet->id],
        'other_category' => ['id' => $otherCategory->id],
    ]);

    if ($case['actor'] === 'admin') {
        $this->actingAs(adminUser());
    } elseif ($case['actor'] === 'user') {
        $this->actingAs($user);
    }

    $endpoint = $case['request']['endpoint'];
    foreach ($case['request']['path'] as $name => $value) {
        $endpoint = str_replace('{'.$name.'}', (string) $value, $endpoint);
    }

    $original = $transaction->only(['user_id', 'wallet_id', 'category_id', 'type', 'amount', 'status', 'note', 'labels']);
    $response = $this->json(
        $case['request']['method'],
        $endpoint,
        $case['request']['body'],
        $case['request']['headers'],
    );

    TestResponseAssertions::assertForCase($response, $case);

    if ($case['expected']['database_change']['operation'] === 'update') {
        $updated = $transaction->fresh()->load(['user', 'wallet', 'category']);

        expect($updated->user->is($user))->toBeTrue()
            ->and($updated->wallet->is($wallet))->toBeTrue();

        $balanceChange = $updated->status === 'posted'
            ? (float) $updated->amount * ($updated->type === 'income' ? 1 : -1)
            : 0.0;
        $this->assertEqualsWithDelta(100 + $balanceChange, $wallet->fresh()->currentBalance(), 0.001);
    } else {
        expect($transaction->fresh()->only(array_keys($original)))->toBe($original);
    }
})->with(TestData::load('admin/transactions/update.json'));
