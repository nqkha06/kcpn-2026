<?php

namespace App\Policies;

use App\Models\Setting;
use App\Models\User;

class SettingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function view(User $user, Setting $setting): bool
    {
        return $user->hasRole('admin');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function update(User $user, Setting $setting): bool
    {
        return $user->hasRole('admin');
    }

    public function delete(User $user, Setting $setting): bool
    {
        return $user->hasRole('admin');
    }
}
