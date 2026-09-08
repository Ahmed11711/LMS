<?php

namespace App\Repositories\Setting;

use App\Repositories\Setting\SettingRepositoryInterface;
use App\Repositories\BaseRepository\BaseRepository;
use App\Models\Setting;

class SettingRepository extends BaseRepository implements SettingRepositoryInterface
{
    public function __construct(Setting $model)
    {
        parent::__construct($model);
    }
}
