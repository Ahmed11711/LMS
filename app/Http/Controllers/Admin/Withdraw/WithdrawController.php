<?php

namespace App\Http\Controllers\Admin\Withdraw;

use App\Http\Controllers\BaseController\BaseController;
use App\Http\Requests\Admin\UserWithdraw\UserWithdrawStoreRequest;
use App\Http\Requests\Admin\Withdraw\WithdrawRequest;
use App\Http\Resources\Admin\UserWithdraw\UserWithdrawResource;
use App\Repositories\UserWithdraw\UserWithdrawRepositoryInterface;


class WithdrawController  extends BaseController
{
    public function __construct(UserWithdrawRepositoryInterface $repository)
    {
        parent::__construct();

        $this->initService(
            repository: $repository,
            collectionName: 'UserWithdraw',
            fileFields: ['receipt_image'],


        );
        $this->withRelationships = ['user:id,name,email'];
        $this->storeRequestClass = UserWithdrawStoreRequest::class;
        $this->updateRequestClass = WithdrawRequest::class;
        $this->resourceClass = UserWithdrawResource::class;
    }
}
