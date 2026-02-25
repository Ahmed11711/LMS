<?php

namespace App\Repositories\UserPackage;

use App\Repositories\UserPackage\UserPackageRepositoryInterface;
use App\Repositories\BaseRepository\BaseRepository;
use App\Models\UserPackage;

class UserPackageRepository extends BaseRepository implements UserPackageRepositoryInterface
{
    public function __construct(UserPackage $model)
    {
        parent::__construct($model);
    }
}
