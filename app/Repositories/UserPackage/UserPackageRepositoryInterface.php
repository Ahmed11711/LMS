<?php

namespace App\Repositories\UserPackage;

use App\Repositories\BaseRepository\BaseRepositoryInterface;

interface UserPackageRepositoryInterface extends BaseRepositoryInterface
{
    public function MyPackage($userId);
}
