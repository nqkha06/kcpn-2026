<?php

namespace App\Services;

use App\Attributes\ListQueryConfig;
use App\Repositories\Contracts\ExpenseTransactionRepositoryInterface as ExpenseTransactionRepository;

#[ListQueryConfig(
    alias: 'transactions',
    searchable: ['id', 'note', 'user.name', 'user.email', 'wallet.name', 'category.name'],
    filterable: ['type', 'status', 'user_id', 'wallet_id', 'category_id'],
    sortable: ['id', 'type', 'amount', 'status', 'transacted_at', 'created_at'],
    selectable: ['id', 'user_id', 'wallet_id', 'category_id', 'type', 'amount', 'status', 'note', 'labels', 'transacted_at', 'created_at'],
    relations: ['user', 'wallet', 'category'],
    defaultSort: [['field' => 'transacted_at', 'direction' => 'desc']],
    dateField: 'transacted_at',
    maxLimit: 100,
)]
class TransactionService extends BaseService
{
    // protected $with = [
    //     'user:id,name,email',
    //     'wallet:id,user_id,name,currency',
    //     'category:id,name,color',
    // ];

    public function __construct(ExpenseTransactionRepository $repository)
    {
        parent::__construct($repository);
    }
}
