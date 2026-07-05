<?php

namespace App\Http\Controllers\Admin\UserSubscribe;

use App\Repositories\UserSubscribe\UserSubscribeRepositoryInterface;
use App\Http\Controllers\BaseController\BaseController;
use App\Http\Requests\Admin\UserSubscribe\UserSubscribeStoreRequest;
use App\Http\Requests\Admin\UserSubscribe\UserSubscribeUpdateRequest;
use App\Http\Resources\Admin\UserSubscribe\UserSubscribeResource;

class UserSubscribeController extends BaseController
{
    public function __construct(UserSubscribeRepositoryInterface $repository)
    {
        parent::__construct();

        $this->initService(
            repository: $repository,
            collectionName: 'UserSubscribe'
        );


        $this->storeRequestClass = UserSubscribeStoreRequest::class;
        $this->updateRequestClass = UserSubscribeUpdateRequest::class;
        $this->resourceClass = UserSubscribeResource::class;
        $this->withRelationships = ['course:id,title', 'user:id,name,email'];
    }
}
