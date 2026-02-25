<?php

namespace App\Repositories\Features;

use App\Models\Central\Features;
use App\Repositories\BaseRepository\BaseRepository;
use App\Repositories\Features\FeaturesRepositoryInterface;

class FeaturesRepository extends BaseRepository implements FeaturesRepositoryInterface
{
    public function __construct(Features $model)
    {
        parent::__construct($model);
    }
}
