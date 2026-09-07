<?php

namespace App\Repositories\CategoryBag;

use App\Repositories\CategoryBag\CategoryBagRepositoryInterface;
use App\Repositories\BaseRepository\BaseRepository;
use App\Models\CategoryBag;

class CategoryBagRepository extends BaseRepository implements CategoryBagRepositoryInterface
{
    public function __construct(CategoryBag $model)
    {
        parent::__construct($model);
    }
}
