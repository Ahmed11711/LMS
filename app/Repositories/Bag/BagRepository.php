<?php

namespace App\Repositories\Bag;

use App\Repositories\Bag\BagRepositoryInterface;
use App\Repositories\BaseRepository\BaseRepository;
use App\Models\Bag;

class BagRepository extends BaseRepository implements BagRepositoryInterface
{
    public function __construct(Bag $model)
    {
        parent::__construct($model);
    }
}
