<?php

use App\Models\Category;
use App\Models\ExpenseTransaction;
use App\Models\User;
use App\Models\UserWallet;

test('admin can view transaction index', function () {
    $admin = adminUser();

    $this->actingAs($admin);
    $this->withoutVite();

    $this->get(route('admin.transactions.index'))
        ->assertOk();
});

test('admin can create a transaction', function () {
    $admin = adminUser();
    $user = User::factory()->create();
    $wallet = UserWallet::factory()->create(['user_id' => $user->id]);
    $category = Category::factory()->create();

    $this->actingAs($admin);

    $payload = [
        'user_id' => $user->id,
        'wallet_id' => $wallet->id,
        'category_id' => $category->id,
        'type' => 'expense',
        'amount' => 180000,
        'transacted_at' => now()->toDateString(),
        'status' => 'posted',
        'note' => 'Lunch payment',
        'labels' => ['food', 'daily'],
    ];

    $this->post(route('admin.transactions.store'), $payload)
        ->assertRedirect(route('admin.transactions.index'));

    $transactionExists = ExpenseTransaction::query()
        ->where('user_id', $user->id)
        ->where('wallet_id', $wallet->id)
        ->where('type', 'expense')
        ->where('status', 'posted')
        ->exists();

    expect($transactionExists)->toBeTrue();
});

test('admin can update a transaction', function () {
    $admin = adminUser();
    $user = User::factory()->create();
    $wallet = UserWallet::factory()->create(['user_id' => $user->id]);
    $category = Category::factory()->create();
    $transaction = ExpenseTransaction::factory()->create([
        'user_id' => $user->id,
        'wallet_id' => $wallet->id,
        'category_id' => $category->id,
        'type' => 'expense',
        'status' => 'pending',
    ]);

    $this->actingAs($admin);

    $payload = [
        'user_id' => $user->id,
        'wallet_id' => $wallet->id,
        'category_id' => $category->id,
        'type' => 'expense',
        'amount' => 250000,
        'transacted_at' => now()->toDateString(),
        'status' => 'posted',
        'note' => 'Updated expense',
        'labels' => ['updated'],
    ];

    $this->put(route('admin.transactions.update', $transaction), $payload)
        ->assertRedirect(route('admin.transactions.index'));

    $updatedExists = ExpenseTransaction::query()
        ->whereKey($transaction->id)
        ->where('amount', 250000)
        ->where('status', 'posted')
        ->exists();

    expect($updatedExists)->toBeTrue();
});

test('admin can delete a transaction', function () {
    $admin = adminUser();
    $transaction = ExpenseTransaction::factory()->create();

    $this->actingAs($admin);

    $this->delete(route('admin.transactions.destroy', $transaction))
        ->assertRedirect(route('admin.transactions.index'));

    $transactionStillExists = ExpenseTransaction::query()
        ->whereKey($transaction->id)
        ->exists();

    expect($transactionStillExists)->toBeFalse();
});
