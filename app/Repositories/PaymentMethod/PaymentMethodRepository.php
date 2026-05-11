<?php

namespace App\Repositories\PaymentMethod;

use App\Repositories\PaymentMethod\PaymentMethodRepositoryInterface;
use App\Repositories\BaseRepository\BaseRepository;
use App\Models\PaymentMethod;

class PaymentMethodRepository extends BaseRepository implements PaymentMethodRepositoryInterface
{
    public function __construct(PaymentMethod $model)
    {
        parent::__construct($model);
    }
}
