<?php

namespace App\Repositories\BagPurchase;

use App\Repositories\BagPurchase\BagPurchaseRepositoryInterface;
use App\Repositories\BaseRepository\BaseRepository;
use App\Models\BagPurchase;

class BagPurchaseRepository extends BaseRepository implements BagPurchaseRepositoryInterface
{
    public function __construct(BagPurchase $model)
    {
        parent::__construct($model);
    }
}
