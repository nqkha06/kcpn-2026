<?php

namespace App\Repositories;

use App\Models\Menu;
use App\Repositories\Contracts\MenuRepositoryInterface;

class MenuRepository extends BaseRepository implements MenuRepositoryInterface
{
    protected $model;

    public function __construct(Menu $model)
    {
        $this->model = $model;
    }
}
