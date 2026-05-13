<?php

namespace App\Http\Controllers\Admin\Withdraw;

use App\Http\Controllers\BaseController\BaseController;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserWithdraw\UserWithdrawStoreRequest;
use App\Http\Requests\Admin\UserWithdraw\UserWithdrawUpdateRequest;
use App\Http\Resources\Admin\UserWithdraw\UserWithdrawResource;
use App\Repositories\UserWithdraw\UserWithdrawRepositoryInterface;
use Illuminate\Http\Request;

class WithdrawController  extends BaseController
{
    public function __construct(UserWithdrawRepositoryInterface $repository)
    {
        parent::__construct();

        $this->initService(
            repository: $repository,
            collectionName: 'UserWithdraw'
        );
        $this->isUserBound = true;
        $this->storeRequestClass = UserWithdrawStoreRequest::class;
        $this->updateRequestClass = UserWithdrawUpdateRequest::class;
        $this->resourceClass = UserWithdrawResource::class;
    }
}
