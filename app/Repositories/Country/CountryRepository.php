<?php

namespace App\Repositories\Country;

use App\Repositories\Country\CountryRepositoryInterface;
use App\Repositories\BaseRepository\BaseRepository;
use App\Models\Country;

class CountryRepository extends BaseRepository implements CountryRepositoryInterface
{
    public function __construct(Country $model)
    {
        parent::__construct($model);
    }
}
