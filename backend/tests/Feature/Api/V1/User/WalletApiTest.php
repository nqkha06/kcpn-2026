<?php

use App\Models\Category;
use App\Models\ExpenseTransaction;
use App\Models\User;
use App\Models\UserWallet;
use Spatie\Permission\Models\Role;

function walletApiUser(string $role = 'user'): User
{
    $user = User::factory()->create();
    $user->assignRole(Role::findOrCreate($role, 'web'));

    return $user;
}

beforeEach(function (): void {
    $this->withHeaders([
        'Accept' => 'application/json',
        'Origin' => 'http://localhost:3000',
        'Referer' => 'http://localhost:3000/user/wallets',
    ]);
});

test('wallet endpoints require authentication and a finance role', function () {
    $this->getJson('/api/v1/user/wallets')
        ->assertUnauthorized()
        ->assertJsonPath('success', false);

    $this->actingAs(User::factory()->create(), 'web')
        ->getJson('/api/v1/user/wallets')
        ->assertForbidden()
        ->assertJsonPath('success', false);
});

test('user only receives own wallets with calculated balances', function () {
    $user = walletApiUser();
    $otherUser = walletApiUser();
    $wallet = UserWallet::factory()->for($user)->defaultWallet()->create([
        'opening_balance' => 1000,
    ]);
    UserWallet::factory()->for($otherUser)->create();
    $category = Category::factory()->create();

    ExpenseTransaction::factory()->forUser($user)->income()->posted()->create([
        'wallet_id' => $wallet->id,
        'category_id' => $category->id,
        'amount' => 500,
    ]);
    ExpenseTransaction::factory()->forUser($user)->expense()->posted()->create([
        'wallet_id' => $wallet->id,
        'category_id' => $category->id,
        'amount' => 200,
    ]);

    $this->actingAs($user, 'web')
        ->getJson('/api/v1/user/wallets')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $wallet->id)
        ->assertJsonPath('data.0.current_balance', 1300)
        ->assertJsonPath('data.0.is_default', true);
});

test('user can create and update wallets while preserving one default wallet', function () {
    $user = walletApiUser();
    $this->actingAs($user, 'web');

    $firstWalletResponse = $this->postJson('/api/v1/user/wallets', [
        'name' => 'Cash',
        'currency' => 'vnd',
        'opening_balance' => 150000,
        'is_default' => false,
    ]);

    $firstWalletResponse
        ->assertCreated()
        ->assertJsonPath('data.currency', 'VND')
        ->assertJsonPath('data.is_default', true);

    $firstWallet = UserWallet::query()->findOrFail($firstWalletResponse->json('data.id'));

    $secondWalletResponse = $this->postJson('/api/v1/user/wallets', [
        'name' => 'Bank',
        'currency' => 'usd',
        'opening_balance' => 100,
        'is_default' => true,
    ]);

    $secondWalletResponse
        ->assertCreated()
        ->assertJsonPath('data.is_default', true);

    expect($firstWallet->fresh()->is_default)->toBeFalse();

    $this->patchJson('/api/v1/user/wallets/'.$firstWallet->id, [
        'name' => 'Daily Cash',
        'currency' => 'eur',
        'opening_balance' => 200,
        'is_default' => true,
    ])
        ->assertOk()
        ->assertJsonPath('data.name', 'Daily Cash')
        ->assertJsonPath('data.currency', 'EUR')
        ->assertJsonPath('data.is_default', true);

    expect(UserWallet::query()->where('user_id', $user->id)->where('is_default', true)->count())
        ->toBe(1);
});

test('wallet validation and ownership are enforced by the backend', function () {
    $user = walletApiUser();
    $otherUser = walletApiUser();
    $wallet = UserWallet::factory()->for($user)->create(['name' => 'Cash']);
    $otherWallet = UserWallet::factory()->for($otherUser)->create();

    $this->actingAs($user, 'web')
        ->postJson('/api/v1/user/wallets', [
            'name' => 'Cash',
            'currency' => 'VN',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name', 'currency']);

    $this->patchJson('/api/v1/user/wallets/'.$otherWallet->id, [
        'name' => 'Stolen',
        'currency' => 'USD',
    ])
        ->assertForbidden()
        ->assertJsonPath('message', 'Forbidden');

    $this->deleteJson('/api/v1/user/wallets/'.$otherWallet->id)
        ->assertForbidden()
        ->assertJsonPath('message', 'Forbidden');

    expect($wallet->fresh())->not->toBeNull();
});

test('deleting the default wallet promotes the oldest remaining wallet', function () {
    $user = walletApiUser();
    $defaultWallet = UserWallet::factory()->for($user)->defaultWallet()->create();
    $remainingWallet = UserWallet::factory()->for($user)->create(['is_default' => false]);

    $this->actingAs($user, 'web')
        ->deleteJson('/api/v1/user/wallets/'.$defaultWallet->id)
        ->assertOk()
        ->assertJsonPath('message', 'Wallet deleted successfully');

    $this->assertSoftDeleted($defaultWallet);
    expect($remainingWallet->fresh()->is_default)->toBeTrue();
});
