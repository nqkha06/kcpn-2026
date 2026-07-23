<?php

namespace App\Services;

use App\Attributes\ListQueryConfig;
use App\Repositories\Contracts\BudgetRepositoryInterface as BudgetRepository;

#[ListQueryConfig(
    alias: 'budgets',
    searchable: ['id', 'note', 'user.name', 'user.email', 'category.name'],
    filterable: ['period', 'status', 'user_id', 'category_id'],
    sortable: ['id', 'amount_limit', 'period', 'status', 'created_at'],
    selectable: ['id', 'user_id', 'category_id', 'amount_limit', 'period', 'status', 'note', 'created_at'],
    relations: ['user', 'category'],
    defaultSort: [['field' => 'created_at', 'direction' => 'desc']],
    dateField: 'created_at',
    maxLimit: 100,
)]
class BudgetService extends BaseService
{
    // protected $with = ['user:id,name,email', 'category:id,name,color'];

    public function __construct(BudgetRepository $repository)
    {
        parent::__construct($repository);
    }
}
