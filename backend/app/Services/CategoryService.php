<?php

namespace App\Services;

use App\Attributes\ListQueryConfig;
use App\Repositories\Contracts\CategoryRepositoryInterface as CategoryRepository;

#[ListQueryConfig(
    alias: 'categories',
    searchable: ['name', 'id'],
    filterable: ['status'],
    sortable: ['id', 'name', 'status', 'created_at'],
    selectable: ['id', 'name', 'color', 'description', 'status', 'created_at'],
    relations: [],
    defaultSort: [['field' => 'created_at', 'direction' => 'desc']],
    dateField: 'created_at',
    maxLimit: 100,
)]

class CategoryService extends BaseService
{
    public function __construct(CategoryRepository $repository)
    {
        parent::__construct($repository);
    }
}
