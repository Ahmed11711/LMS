<?php

namespace App\Repositories\UserWithdraw;

use App\Repositories\UserWithdraw\UserWithdrawRepositoryInterface;
use App\Repositories\BaseRepository\BaseRepository;
use App\Models\UserWithdraw;

class UserWithdrawRepository extends BaseRepository implements UserWithdrawRepositoryInterface
{
    public function __construct(UserWithdraw $model)
    {
        parent::__construct($model);
    }
}
