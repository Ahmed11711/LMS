<?php

namespace App\Repositories\Package;

use App\Models\Central\Package;
use App\Repositories\BaseRepository\BaseRepository;
use App\Repositories\Package\PackageRepositoryInterface;

class PackageRepository extends BaseRepository implements PackageRepositoryInterface
{
    public function __construct(Package $model)
    {
        parent::__construct($model);
    }
}
