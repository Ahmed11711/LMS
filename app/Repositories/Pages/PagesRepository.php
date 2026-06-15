<?php

namespace App\Repositories\Pages;

use App\Repositories\Pages\PagesRepositoryInterface;
use App\Repositories\BaseRepository\BaseRepository;
use App\Models\Pages;

class PagesRepository extends BaseRepository implements PagesRepositoryInterface
{
    public function __construct(Pages $model)
    {
        parent::__construct($model);
    }
}
