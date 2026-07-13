<?php

namespace App\Repositories\LandingPage;

use App\Repositories\LandingPage\LandingPageRepositoryInterface;
use App\Repositories\BaseRepository\BaseRepository;
use App\Models\LandingPage;

class LandingPageRepository extends BaseRepository implements LandingPageRepositoryInterface
{
    public function __construct(LandingPage $model)
    {
        parent::__construct($model);
    }
}
