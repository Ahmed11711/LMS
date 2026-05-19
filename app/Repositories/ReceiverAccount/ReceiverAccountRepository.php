<?php

namespace App\Repositories\ReceiverAccount;

use App\Repositories\ReceiverAccount\ReceiverAccountRepositoryInterface;
use App\Repositories\BaseRepository\BaseRepository;
use App\Models\ReceiverAccount;

class ReceiverAccountRepository extends BaseRepository implements ReceiverAccountRepositoryInterface
{
    public function __construct(ReceiverAccount $model)
    {
        parent::__construct($model);
    }
}
