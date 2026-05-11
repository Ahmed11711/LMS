<?php

namespace App\Repositories\UserPaymentInfo;

use App\Repositories\UserPaymentInfo\UserPaymentInfoRepositoryInterface;
use App\Repositories\BaseRepository\BaseRepository;
use App\Models\UserPaymentInfo;

class UserPaymentInfoRepository extends BaseRepository implements UserPaymentInfoRepositoryInterface
{
    public function __construct(UserPaymentInfo $model)
    {
        parent::__construct($model);
    }
}
