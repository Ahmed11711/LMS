<?php

namespace App\Repositories\AcademyPaymentMethod;

use App\Repositories\AcademyPaymentMethod\AcademyPaymentMethodRepositoryInterface;
use App\Repositories\BaseRepository\BaseRepository;
use App\Models\AcademyPaymentMethod;

class AcademyPaymentMethodRepository extends BaseRepository implements AcademyPaymentMethodRepositoryInterface
{
    public function __construct(AcademyPaymentMethod $model)
    {
        parent::__construct($model);
    }
}
