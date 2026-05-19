<?php

namespace App\Http\Controllers\Admin\PaymentMethod;

use App\Repositories\PaymentMethod\PaymentMethodRepositoryInterface;
use App\Http\Controllers\BaseController\BaseController;
use App\Http\Requests\Admin\PaymentMethod\PaymentMethodStoreRequest;
use App\Http\Requests\Admin\PaymentMethod\PaymentMethodUpdateRequest;
use App\Http\Resources\Admin\PaymentMethod\PaymentMethodResource;

class PaymentMethodController extends BaseController
{
    public function __construct(PaymentMethodRepositoryInterface $repository)
    {
        parent::__construct();

        $this->initService(
            repository: $repository,
            collectionName: 'PaymentMethod'
        );

        $this->storeRequestClass = PaymentMethodStoreRequest::class;
        $this->updateRequestClass = PaymentMethodUpdateRequest::class;
        $this->resourceClass = PaymentMethodResource::class;
    }
}
