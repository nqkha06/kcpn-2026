<?php

namespace App\Services\Auth;

use App\Models\User;

final readonly class LoginResult
{
    public function __construct(
        public ?User $user,
        public bool $requiresTwoFactor,
    ) {}
}
