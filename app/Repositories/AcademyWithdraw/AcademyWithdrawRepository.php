<?php

namespace App\Repositories\AcademyWithdraw;

use App\Repositories\AcademyWithdraw\AcademyWithdrawRepositoryInterface;
use App\Repositories\BaseRepository\BaseRepository;
use App\Models\AcademyWithdraw;

class AcademyWithdrawRepository extends BaseRepository implements AcademyWithdrawRepositoryInterface
{
    public function __construct(AcademyWithdraw $model)
    {
        parent::__construct($model);
    }
}
