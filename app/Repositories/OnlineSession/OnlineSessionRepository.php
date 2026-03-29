<?php

namespace App\Repositories\OnlineSession;

use App\Repositories\OnlineSession\OnlineSessionRepositoryInterface;
use App\Repositories\BaseRepository\BaseRepository;
use App\Models\OnlineSession;

class OnlineSessionRepository extends BaseRepository implements OnlineSessionRepositoryInterface
{
    public function __construct(OnlineSession $model)
    {
        parent::__construct($model);
    }
}
