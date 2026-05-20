<?php

namespace App\Repositories\Plan;

use App\Repositories\Plan\PlanRepositoryInterface;
use App\Repositories\BaseRepository\BaseRepository;
use App\Models\Plan;

class PlanRepository extends BaseRepository implements PlanRepositoryInterface
{
    public function __construct(Plan $model)
    {
        parent::__construct($model);
    }

    public function findActive(int $planId): mixed
    {
        return $this->model
            ->where('id', $planId)
            ->where('status', 'active')
            ->first();
    }
}
