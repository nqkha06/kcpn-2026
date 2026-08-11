<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(Tests\TestCase::class)
    ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function adminUser(): App\Models\User
{
    app(Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

    $user = App\Models\User::factory()->create();
    $user->assignRole(Spatie\Permission\Models\Role::findOrCreate('admin', 'web'));

    return $user;
}

function regularUser(): App\Models\User
{
    app(Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

    $user = App\Models\User::factory()->create();
    $user->assignRole(Spatie\Permission\Models\Role::findOrCreate('user', 'web'));

    return $user;
}
