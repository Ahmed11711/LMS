<?php

namespace App\Repositories\UserPackage;

use App\Repositories\UserPackage\UserPackageRepositoryInterface;
use App\Repositories\BaseRepository\BaseRepository;
use App\Models\Central\UserPackage;

class UserPackageRepository extends BaseRepository implements UserPackageRepositoryInterface
{
    public function __construct(UserPackage $model)
    {
        parent::__construct($model);
    }

    public function MyPackage($userId)
    {

        return $this->model->where('user_id', $userId)->where('active')->first();
    }
}
