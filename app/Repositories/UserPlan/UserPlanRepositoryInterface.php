<?php

namespace App\Repositories\UserPlan;

use App\Repositories\BaseRepository\BaseRepositoryInterface;

interface UserPlanRepositoryInterface extends BaseRepositoryInterface
{
    public function isAlreadySubscribed(int $userId, int $planId): bool;
}
