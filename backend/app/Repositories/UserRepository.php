<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;

/**
 * Class UserRepository.
 */
class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    protected $model;

    public function __construct(User $model)
    {
        $this->model = $model;
    }

    public function pagination(array $specification = [])
    {
        $query = $this->getQuery();
        if (! empty($specification['filters']['complex']['roles']['in'])) {
            $query = $query->role($specification['filters']['complex']['roles']['in'] ?? null);
        }

        return parent::pagination($specification);
    }
}
