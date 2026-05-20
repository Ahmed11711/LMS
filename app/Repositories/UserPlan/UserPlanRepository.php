<?php

namespace App\Repositories\UserPlan;

use App\Repositories\BaseRepository\BaseRepository;
use App\Models\UserPlan;

class UserPlanRepository extends BaseRepository implements UserPlanRepositoryInterface
{
    public function __construct(UserPlan $model)
    {
        parent::__construct($model);
    }

    public function isAlreadySubscribed(int $userId, int $planId): bool
    {
        return $this->model
            ->where('user_id', $userId)
            ->where('plan_id', $planId)
            ->where('status', 'active')
            ->exists();
    }

    public function findActive(int $planId): mixed
    {
        return $this->model
            ->where('id', $planId)
            ->where('status', 'active')
            ->first();
    }
}
