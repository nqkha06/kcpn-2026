<?php

namespace App\Repositories;

use App\Models\ExpenseTransaction;
use App\Repositories\Contracts\ExpenseTransactionRepositoryInterface;

class ExpenseTransactionRepository extends BaseRepository implements ExpenseTransactionRepositoryInterface
{
    protected $model;

    public function __construct(ExpenseTransaction $model)
    {
        $this->model = $model;
    }
}
