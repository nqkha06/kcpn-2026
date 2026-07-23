<?php

namespace App\Services;

use App\Attributes\ListQueryConfig;
use App\Repositories\Contracts\UserRepositoryInterface as UserRepository;

/**
 * Class UserService
 */
#[ListQueryConfig(
    alias: 'users',
    searchable: ['name', 'email', 'id'],
    filterable: ['status', 'email', 'roles.name'],
    sortable: ['id', 'name', 'email', 'created_at'],
    selectable: ['id', 'name', 'email', 'status', 'created_at'],
    relations: ['roles'],
    defaultSort: [['field' => 'id', 'direction' => 'desc']],
    dateField: 'created_at',
    maxLimit: 100,
)]

class UserService extends BaseService
{
    public function __construct(UserRepository $repository)
    {
        parent::__construct($repository);
    }
}
