<?php

use App\Models\Category;
use App\Models\Menu;
use App\Models\User;
use Spatie\Permission\Models\Role;

function contentApiActor(string $role): User
{
    $user = User::factory()->create();
    $user->assignRole(Role::findOrCreate($role, 'web'));

    return $user;
}

beforeEach(function (): void {
    $this->withHeaders([
        'Accept' => 'application/json',
        'Origin' => 'http://localhost:3000',
        'Referer' => 'http://localhost:3000/admin',
    ]);
});

test('admin content routes reject guests and non admin users', function () {
    $this->getJson('/api/v1/admin/pages')
        ->assertUnauthorized()
        ->assertJsonPath('message', 'Unauthenticated');

    $this->actingAs(contentApiActor('user'), 'web')
        ->getJson('/api/v1/admin/pages')
        ->assertForbidden()
        ->assertJsonPath('message', 'Forbidden');
});

test('admin can create list show update and delete pages', function () {
    $admin = contentApiActor('admin');
    $category = Category::factory()->create();
    $this->actingAs($admin, 'web');

    $payload = [
        'title' => 'Getting Started',
        'content' => '<p>Original content</p>',
        'category_id' => $category->id,
        'tags' => 'guide, guide, finance',
        'status' => 'published',
    ];

    $created = $this->postJson('/api/v1/admin/pages', $payload)
        ->assertCreated()
        ->assertJsonPath('data.slug', 'getting-started')
        ->assertJsonPath('data.tags', ['guide', 'finance'])
        ->assertJsonPath('data.author.id', $admin->id)
        ->assertJsonPath('data.category.id', $category->id);

    $pageId = $created->json('data.id');

    $this->postJson('/api/v1/admin/pages', $payload)
        ->assertCreated()
        ->assertJsonPath('data.slug', 'getting-started-1');

    $this->getJson('/api/v1/admin/pages?search=Getting&status=published&sort=title&direction=asc&per_page=1')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('meta.total', 2)
        ->assertJsonPath('meta.per_page', 1);

    $this->getJson('/api/v1/admin/pages/'.$pageId)
        ->assertOk()
        ->assertJsonPath('data.content', '<p>Original content</p>');

    $this->patchJson('/api/v1/admin/pages/'.$pageId, [
        ...$payload,
        'title' => 'Updated Guide',
        'slug' => 'updated-guide',
        'status' => 'draft',
    ])
        ->assertOk()
        ->assertJsonPath('data.title', 'Updated Guide')
        ->assertJsonPath('data.slug', 'updated-guide')
        ->assertJsonPath('data.status', 'draft');

    $this->deleteJson('/api/v1/admin/pages/'.$pageId)
        ->assertOk()
        ->assertJsonPath('message', 'Page deleted successfully');

    $this->assertDatabaseMissing('pages', ['id' => $pageId]);
});

test('page api preserves validation rules and query validation', function () {
    $admin = contentApiActor('admin');
    $this->actingAs($admin, 'web');

    $this->postJson('/api/v1/admin/pages', [
        'title' => '',
        'status' => 'invalid',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['title', 'status']);

    $this->getJson('/api/v1/admin/pages?sort=user_id&per_page=500')
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['sort', 'per_page']);
});

test('admin can perform category crud with pagination and filters', function () {
    $admin = contentApiActor('admin');
    $this->actingAs($admin, 'web');

    $created = $this->postJson('/api/v1/admin/categories', [
        'name' => 'Food',
        'color' => '#10B981',
        'description' => 'Food expenses',
        'status' => 'active',
    ])
        ->assertCreated()
        ->assertJsonPath('data.name', 'Food');

    $categoryId = $created->json('data.id');
    Category::factory()->inactive()->create(['name' => 'Hidden category']);

    $this->getJson('/api/v1/admin/categories?search=Food&status=active&sort=name&direction=asc')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $categoryId)
        ->assertJsonPath('meta.total', 1);

    $this->patchJson('/api/v1/admin/categories/'.$categoryId, [
        'name' => 'Dining',
        'color' => '#3B82F6',
        'description' => null,
        'status' => 'inactive',
    ])
        ->assertOk()
        ->assertJsonPath('data.name', 'Dining')
        ->assertJsonPath('data.status', 'inactive');

    $this->deleteJson('/api/v1/admin/categories/'.$categoryId)
        ->assertOk();

    $this->assertDatabaseMissing('categories', ['id' => $categoryId]);
});

test('admin menu crud normalizes child canonical and exposes parent options', function () {
    $admin = contentApiActor('admin');
    $parent = Menu::factory()->header()->create(['title' => 'Main']);
    $this->actingAs($admin, 'web');

    $created = $this->postJson('/api/v1/admin/menus', [
        'title' => 'About',
        'url' => ' /p/about ',
        'parent_id' => $parent->id,
        'canonical' => 'home.footer',
        'sort_order' => 5,
        'target' => '_self',
        'status' => 'active',
    ])
        ->assertCreated()
        ->assertJsonPath('data.url', '/p/about')
        ->assertJsonPath('data.canonical', 'home.header')
        ->assertJsonPath('data.parent.id', $parent->id);

    $menuId = $created->json('data.id');

    $this->getJson('/api/v1/admin/menus?canonical=home.header&status=active&sort=sort_order&direction=asc')
        ->assertOk()
        ->assertJsonPath('meta.total', 2);

    $this->getJson('/api/v1/admin/menus/parent-options?exclude='.$parent->id)
        ->assertOk()
        ->assertJsonMissing(['id' => $parent->id]);

    $this->patchJson('/api/v1/admin/menus/'.$menuId, [
        'title' => 'About Us',
        'url' => '',
        'parent_id' => null,
        'canonical' => 'home.footer',
        'sort_order' => 1,
        'target' => '_blank',
        'status' => 'inactive',
    ])
        ->assertOk()
        ->assertJsonPath('data.title', 'About Us')
        ->assertJsonPath('data.url', null)
        ->assertJsonPath('data.canonical', 'home.footer');

    $this->deleteJson('/api/v1/admin/menus/'.$menuId)
        ->assertOk()
        ->assertJsonPath('message', 'Menu deleted successfully');

    $this->assertDatabaseMissing('menus', ['id' => $menuId]);
});
