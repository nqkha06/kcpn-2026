<?php

namespace App\Repositories;

use App\Models\Budget;
use App\Repositories\Contracts\BudgetRepositoryInterface;

class BudgetRepository extends BaseRepository implements BudgetRepositoryInterface
{
    protected $model;

    public function __construct(Budget $model)
    {
        $this->model = $model;
    }
}
