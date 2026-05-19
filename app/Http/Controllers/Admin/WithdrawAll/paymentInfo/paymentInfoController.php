<?php

namespace App\Http\Controllers\Admin\paymentInfo;

use App\Repositories\paymentInfo\paymentInfoRepositoryInterface;
use App\Http\Controllers\BaseController\BaseController;
use App\Http\Requests\Admin\paymentInfo\paymentInfoStoreRequest;
use App\Http\Requests\Admin\paymentInfo\paymentInfoUpdateRequest;
use App\Http\Resources\Admin\paymentInfo\paymentInfoResource;

class paymentInfoController extends BaseController
{
    public function __construct(paymentInfoRepositoryInterface $repository)
    {
        parent::__construct();

        $this->initService(
            repository: $repository,
            collectionName: 'paymentInfo'
        );

        $this->storeRequestClass = paymentInfoStoreRequest::class;
        $this->updateRequestClass = paymentInfoUpdateRequest::class;
        $this->resourceClass = paymentInfoResource::class;
    }
}
