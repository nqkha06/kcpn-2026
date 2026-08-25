<?php

use App\Models\Category;
use App\Models\ExpenseTransaction;
use App\Models\UserWallet;
use Tests\Support\TestData;
use Tests\Support\TestResponseAssertions;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\postJson;

test('an admin can create a transaction', function () {
    $user = regularUser();
    $wallet = UserWallet::factory()->for($user)->create();
    $category = Category::factory()->create();

    actingAs(adminUser())
        ->postJson('/api/v1/admin/transactions', [
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'category_id' => $category->id,
            'type' => 'expense',
            'amount' => 150000,
            'transacted_at' => '2026-07-29',
            'status' => 'posted',
            'note' => 'Admin created',
            'labels' => ['office'],
        ])
        ->assertCreated()
        ->assertJsonPath('data.user_id', $user->id);

    assertDatabaseHas('expense_transactions', ['user_id' => $user->id, 'amount' => 150000]);
});

test('a guest cannot create an admin transaction', function () {
    $user = regularUser();
    $wallet = UserWallet::factory()->for($user)->create();

    postJson('/api/v1/admin/transactions', [
        'user_id' => $user->id,
        'wallet_id' => $wallet->id,
        'type' => 'expense',
        'amount' => 100,
        'transacted_at' => '2026-08-01',
        'status' => 'posted',
    ])->assertUnauthorized();

    assertDatabaseMissing('expense_transactions', ['wallet_id' => $wallet->id]);
});

test('a regular user cannot create an admin transaction', function () {
    $user = regularUser();
    $wallet = UserWallet::factory()->for($user)->create();

    actingAs($user)
        ->postJson('/api/v1/admin/transactions', [
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'type' => 'expense',
            'amount' => 100,
            'transacted_at' => '2026-08-01',
            'status' => 'posted',
        ])
        ->assertForbidden();
});

test('admin transaction creation validates required fields', function () {
    actingAs(adminUser())
        ->postJson('/api/v1/admin/transactions', [])
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

test('admin transaction creation rejects invalid values', function () {
    $user = regularUser();
    $wallet = UserWallet::factory()->for($user)->create();

    actingAs(adminUser())
        ->postJson('/api/v1/admin/transactions', [
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'type' => 'transfer',
            'amount' => 0,
            'transacted_at' => 'not-a-date',
            'status' => 'approved',
            'labels' => ['a-label-that-is-longer-than-thirty-characters'],
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['type', 'amount', 'transacted_at', 'status', 'labels.0']);
});

test('an admin cannot assign a wallet owned by another user', function () {
    $selectedUser = regularUser();
    $otherWallet = UserWallet::factory()->for(regularUser())->create();

    actingAs(adminUser())
        ->postJson('/api/v1/admin/transactions', [
            'user_id' => $selectedUser->id,
            'wallet_id' => $otherWallet->id,
            'type' => 'expense',
            'amount' => 100,
            'transacted_at' => '2026-08-01',
            'status' => 'posted',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('wallet_id');
});

test('an admin cannot assign another users private category', function () {
    $selectedUser = regularUser();
    $wallet = UserWallet::factory()->for($selectedUser)->create();
    $privateCategory = Category::factory()->create(['user_id' => regularUser()->id]);

    actingAs(adminUser())
        ->postJson('/api/v1/admin/transactions', [
            'user_id' => $selectedUser->id,
            'wallet_id' => $wallet->id,
            'category_id' => $privateCategory->id,
            'type' => 'expense',
            'amount' => 100,
            'transacted_at' => '2026-08-01',
            'status' => 'posted',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('category_id');
});

test('admin transaction creation normalizes optional fields', function () {
    $user = regularUser();
    $wallet = UserWallet::factory()->for($user)->create();

    actingAs(adminUser())
        ->postJson('/api/v1/admin/transactions', [
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'category_id' => 'none',
            'type' => 'income',
            'amount' => 125.5,
            'transacted_at' => '2026-08-01',
            'status' => 'pending',
            'note' => '   ',
            'labels' => ' work, recurring, work ',
        ])
        ->assertCreated()
        ->assertJsonPath('data.category_id', null)
        ->assertJsonPath('data.note', null)
        ->assertJsonPath('data.labels', ['work', 'recurring']);

    assertDatabaseHas('expense_transactions', [
        'wallet_id' => $wallet->id,
        'category_id' => null,
        'note' => null,
    ]);
});

test('admin transaction create follows shared execution data', function (array $case) {
    $user = regularUser();
    $wallet = UserWallet::factory()->for($user)->create([
        'opening_balance' => 100,
        'is_default' => true,
    ]);
    $category = Category::factory()->create(['status' => 'active']);
    $userCategory = Category::factory()->create(['user_id' => $user->id, 'status' => 'active']);
    $otherUser = regularUser();
    $otherWallet = UserWallet::factory()->for($otherUser)->create();
    $otherCategory = Category::factory()->create(['user_id' => $otherUser->id]);

    $case = TestData::resolveAliases($case, [
        'user' => ['id' => $user->id],
        'wallet' => ['id' => $wallet->id],
        'category' => ['id' => $category->id],
        'user_category' => ['id' => $userCategory->id],
        'other_wallet' => ['id' => $otherWallet->id],
        'other_category' => ['id' => $otherCategory->id],
        'missing' => ['id' => 999_999_999],
    ]);

    if ($case['actor'] === 'admin') {
        $this->actingAs(adminUser());
    } elseif ($case['actor'] === 'user') {
        $this->actingAs($user);
    }

    $beforeCount = ExpenseTransaction::query()->count();
    $response = $this->json(
        $case['request']['method'],
        $case['request']['endpoint'],
        $case['request']['body'],
        $case['request']['headers'],
    );

    TestResponseAssertions::assertForCase($response, $case);

    $expectedDelta = $case['expected']['database_change']['operation'] === 'insert' ? 1 : 0;
    expect(ExpenseTransaction::query()->count())->toBe($beforeCount + $expectedDelta);

    if ($expectedDelta === 1) {
        $transaction = ExpenseTransaction::query()
            ->with(['user', 'wallet', 'category'])
            ->findOrFail($response->json('data.id'));

        expect($transaction->user->is($user))->toBeTrue()
            ->and($transaction->wallet->is($wallet))->toBeTrue();

        if ($transaction->category_id !== null) {
            expect($transaction->category)->not->toBeNull();
        }

        $balanceChange = $transaction->status === 'posted'
            ? (float) $transaction->amount * ($transaction->type === 'income' ? 1 : -1)
            : 0.0;

        $this->assertEqualsWithDelta(100 + $balanceChange, $wallet->fresh()->currentBalance(), 0.001);
    }
})->with(TestData::load('admin/transactions/create.json'));
