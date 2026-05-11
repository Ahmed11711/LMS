<?php

namespace App\Repositories\paymentInfo;

use App\Repositories\paymentInfo\paymentInfoRepositoryInterface;
use App\Repositories\BaseRepository\BaseRepository;
use App\Models\paymentInfo;

class paymentInfoRepository extends BaseRepository implements paymentInfoRepositoryInterface
{
    public function __construct(paymentInfo $model)
    {
        parent::__construct($model);
    }
}
