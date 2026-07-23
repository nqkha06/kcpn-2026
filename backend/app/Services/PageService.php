<?php

namespace App\Services;

use App\Attributes\ListQueryConfig;
use App\Repositories\Contracts\PageRepositoryInterface as PageRepository;

#[ListQueryConfig(
    alias: 'pages',
    searchable: ['id', 'title', 'slug'],
    filterable: ['status'],
    sortable: ['id', 'title', 'slug', 'status', 'created_at'],
    selectable: ['id', 'title', 'slug', 'status', 'created_at'],
    relations: [],
    defaultSort: [['field' => 'created_at', 'direction' => 'desc']],
    dateField: 'created_at',
    maxLimit: 100,
)]

class PageService extends BaseService
{
    public function __construct(PageRepository $repository)
    {
        parent::__construct($repository);
    }
}
