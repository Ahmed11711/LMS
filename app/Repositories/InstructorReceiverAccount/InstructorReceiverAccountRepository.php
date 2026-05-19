<?php

namespace App\Repositories\InstructorReceiverAccount;

use App\Repositories\InstructorReceiverAccount\InstructorReceiverAccountRepositoryInterface;
use App\Repositories\BaseRepository\BaseRepository;
use App\Models\InstructorReceiverAccount;

class InstructorReceiverAccountRepository extends BaseRepository implements InstructorReceiverAccountRepositoryInterface
{
    public function __construct(InstructorReceiverAccount $model)
    {
        parent::__construct($model);
    }
}
