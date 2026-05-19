<?php

namespace App\Http\Controllers\Admin\ReceiverAccount;

use App\Repositories\ReceiverAccount\ReceiverAccountRepositoryInterface;
use App\Http\Controllers\BaseController\BaseController;
use App\Http\Requests\Admin\ReceiverAccount\ReceiverAccountStoreRequest;
use App\Http\Requests\Admin\ReceiverAccount\ReceiverAccountUpdateRequest;
use App\Http\Resources\Admin\ReceiverAccount\ReceiverAccountResource;

class ReceiverAccountController extends BaseController
{
    public function __construct(ReceiverAccountRepositoryInterface $repository)
    {
        parent::__construct();

        $this->initService(
            repository: $repository,
            collectionName: 'ReceiverAccount',
            fileFields: ['logo']


        );

        $this->storeRequestClass = ReceiverAccountStoreRequest::class;
        $this->updateRequestClass = ReceiverAccountUpdateRequest::class;
        $this->resourceClass = ReceiverAccountResource::class;
    }
}
