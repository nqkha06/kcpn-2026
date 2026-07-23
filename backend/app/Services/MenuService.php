<?php

namespace App\Services;

use App\Attributes\ListQueryConfig;
use App\Repositories\Contracts\MenuRepositoryInterface as MenuRepository;

#[ListQueryConfig(
    alias: 'menus',
    searchable: ['id', 'title', 'url'],
    filterable: ['status', 'canonical', 'parent_id'],
    sortable: ['id', 'title', 'canonical', 'sort_order', 'status', 'created_at'],
    selectable: ['id', 'title', 'url', 'parent_id', 'canonical', 'sort_order', 'target', 'status', 'created_at'],
    relations: ['parent'],
    defaultSort: [['field' => 'sort_order', 'direction' => 'asc'], ['field' => 'created_at', 'direction' => 'desc']],
    dateField: 'created_at',
    maxLimit: 100,
)]
class MenuService extends BaseService
{
    public function __construct(MenuRepository $repository)
    {
        parent::__construct($repository);
    }
}
