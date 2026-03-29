<?php

namespace App\Repositories\Package;

use App\Repositories\BaseRepository\BaseRepositoryInterface;

interface PackageRepositoryInterface extends BaseRepositoryInterface
{
    public function getFreePackage();
}
