<?php

use App\Concerns\ProfileValidationRules;

test('profile validation rules support both null and existing user id', function () {
    $class = new class
    {
        use ProfileValidationRules;

        public function getRules(?int $id = null): array
        {
            return $this->profileRules($id);
        }
    };

    $rulesWithoutId = $class->getRules(null);
    expect($rulesWithoutId)->toHaveKeys(['name', 'email'])
        ->and($rulesWithoutId['name'])->toContain('required')
        ->and($rulesWithoutId['email'])->toContain('email');

    $rulesWithId = $class->getRules(123);
    expect($rulesWithId)->toHaveKeys(['name', 'email'])
        ->and($rulesWithId['name'])->toContain('required')
        ->and($rulesWithId['email'])->toContain('email');
});
