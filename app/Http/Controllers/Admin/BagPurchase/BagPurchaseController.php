<?php

namespace App\Http\Controllers\Admin\BagPurchase;

use App\Repositories\BagPurchase\BagPurchaseRepositoryInterface;
use App\Http\Controllers\BaseController\BaseController;
use App\Http\Requests\Admin\BagPurchase\BagPurchaseStoreRequest;
use App\Http\Requests\Admin\BagPurchase\BagPurchaseUpdateRequest;
use App\Http\Resources\Admin\BagPurchase\BagPurchaseResource;

class BagPurchaseController extends BaseController
{
    public function __construct(BagPurchaseRepositoryInterface $repository)
    {
        parent::__construct();

        $this->initService(
            repository: $repository,
            collectionName: 'BagPurchase'
        );

        $this->storeRequestClass = BagPurchaseStoreRequest::class;
        $this->updateRequestClass = BagPurchaseUpdateRequest::class;
        $this->resourceClass = BagPurchaseResource::class;
    }
}
