<?php

use App\Models\Category;
use App\Models\User;
use App\Models\UserWallet;
use Spatie\Permission\Models\Role;

function privateCategoryActor(): User
{
    $user = User::factory()->create();
    $user->assignRole(Role::findOrCreate('user', 'web'));

    return $user;
}

beforeEach(function (): void {
    $this->withHeaders([
        'Accept' => 'application/json',
        'Origin' => 'http://localhost:3001',
        'Referer' => 'http://localhost:3001/categories',
    ]);
});

test('a user only receives system categories and their own private categories', function () {
    $owner = privateCategoryActor();
    $otherUser = privateCategoryActor();
    $systemCategory = Category::factory()->create(['name' => 'System category']);
    $ownCategory = Category::factory()->create(['user_id' => $owner->id, 'name' => 'Owner category']);
    $otherCategory = Category::factory()->create(['user_id' => $otherUser->id, 'name' => 'Other category']);

    $this->actingAs($owner, 'web')
        ->getJson('/api/v1/user/categories')
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonFragment(['id' => $systemCategory->id, 'is_private' => false])
        ->assertJsonFragment(['id' => $ownCategory->id, 'is_private' => true])
        ->assertJsonMissing(['id' => $otherCategory->id]);
});

test('users can create update and delete only their private categories', function () {
    $owner = privateCategoryActor();
    $otherUser = privateCategoryActor();
    $otherCategory = Category::factory()->create(['user_id' => $otherUser->id]);

    $created = $this->actingAs($owner, 'web')->postJson('/api/v1/user/categories', [
        'name' => 'Đi du lịch',
        'color' => '#0EA5E9',
        'description' => 'Các khoản cho chuyến đi',
    ])
        ->assertCreated()
        ->assertJsonPath('data.is_private', true)
        ->assertJsonPath('data.name', 'Đi du lịch');

    $categoryId = $created->json('data.id');
    $this->assertDatabaseHas('categories', ['id' => $categoryId, 'user_id' => $owner->id]);

    $this->patchJson('/api/v1/user/categories/'.$categoryId, [
        'name' => 'Du lịch riêng',
        'color' => '#0284C7',
        'description' => null,
    ])->assertOk()->assertJsonPath('data.name', 'Du lịch riêng');

    $this->patchJson('/api/v1/user/categories/'.$otherCategory->id, [
        'name' => 'Không được phép',
        'color' => '#0284C7',
    ])->assertForbidden();
    $this->deleteJson('/api/v1/user/categories/'.$otherCategory->id)->assertForbidden();

    $this->deleteJson('/api/v1/user/categories/'.$categoryId)->assertOk();
    $this->assertDatabaseMissing('categories', ['id' => $categoryId]);
});

test('private names are unique across the system and own categories visible to a user', function () {
    $firstUser = privateCategoryActor();
    Category::factory()->create(['name' => 'Ăn uống']);

    $payload = ['name' => 'Ăn uống', 'color' => '#F59E0B'];
    $this->actingAs($firstUser, 'web')->postJson('/api/v1/user/categories', $payload)
        ->assertUnprocessable()
        ->assertJsonValidationErrors('name');

    Category::factory()->create(['user_id' => $firstUser->id, 'name' => 'Cá nhân']);
    $this->postJson('/api/v1/user/categories', ['name' => 'Cá nhân', 'color' => '#F59E0B'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('name');
});

test('different users may use the same private category name', function () {
    $firstUser = privateCategoryActor();
    $secondUser = privateCategoryActor();
    Category::factory()->create(['user_id' => $firstUser->id, 'name' => 'Cá nhân']);

    $this->actingAs($secondUser, 'web')
        ->postJson('/api/v1/user/categories', ['name' => 'Cá nhân', 'color' => '#F59E0B'])
        ->assertCreated();
});

test('transactions and budgets reject private categories owned by another user', function () {
    $owner = privateCategoryActor();
    $otherUser = privateCategoryActor();
    $wallet = UserWallet::factory()->create(['user_id' => $owner->id]);
    $ownCategory = Category::factory()->create(['user_id' => $owner->id]);
    $otherCategory = Category::factory()->create(['user_id' => $otherUser->id]);

    $transaction = [
        'wallet_id' => $wallet->id,
        'category_id' => $otherCategory->id,
        'type' => 'expense',
        'amount' => 100,
        'transacted_at' => now()->toDateString(),
    ];
    $this->actingAs($owner, 'web')->postJson('/api/v1/user/transactions', $transaction)
        ->assertUnprocessable()
        ->assertJsonValidationErrors('category_id');

    $this->postJson('/api/v1/user/transactions', [...$transaction, 'category_id' => $ownCategory->id])
        ->assertCreated();

    $budget = ['category_id' => $otherCategory->id, 'amount_limit' => 1000, 'period' => 'monthly'];
    $this->postJson('/api/v1/user/budgets', $budget)
        ->assertUnprocessable()
        ->assertJsonValidationErrors('category_id');

    $this->postJson('/api/v1/user/budgets', [...$budget, 'category_id' => $ownCategory->id])
        ->assertCreated();
});

test('a private category in use cannot be deleted', function () {
    $owner = privateCategoryActor();
    $wallet = UserWallet::factory()->create(['user_id' => $owner->id]);
    $category = Category::factory()->create(['user_id' => $owner->id]);

    $this->actingAs($owner, 'web')->postJson('/api/v1/user/transactions', [
        'wallet_id' => $wallet->id,
        'category_id' => $category->id,
        'type' => 'expense',
        'amount' => 100,
        'transacted_at' => now()->toDateString(),
    ])->assertCreated();

    $this->deleteJson('/api/v1/user/categories/'.$category->id)
        ->assertStatus(409)
        ->assertJsonPath('message', 'Không thể xóa danh mục đang được sử dụng.');
    $this->assertDatabaseHas('categories', ['id' => $category->id]);
});

test('admin category listing excludes user private categories', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::findOrCreate('admin', 'web'));
    $owner = privateCategoryActor();
    $systemCategory = Category::factory()->create(['name' => 'System only']);
    $privateCategory = Category::factory()->create(['user_id' => $owner->id, 'name' => 'Private only']);

    $this->actingAs($admin, 'web')->getJson('/api/v1/admin/categories')
        ->assertOk()
        ->assertJsonFragment(['id' => $systemCategory->id])
        ->assertJsonMissing(['id' => $privateCategory->id]);
});
