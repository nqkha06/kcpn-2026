<?php

namespace App\Repositories;

use App\Models\Page;
use App\Repositories\Contracts\PageRepositoryInterface;

/**
 * Class PageRepository.
 */
class PageRepository extends BaseRepository implements PageRepositoryInterface
{
    protected $model;

    public function __construct(Page $model)
    {
        $this->model = $model;
    }
}
