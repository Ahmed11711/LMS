<?php

namespace App\Http\Controllers\Admin\UserPlan;

use App\Http\Controllers\BaseController\BaseController;
use App\Http\Requests\Admin\UserPlan\UserPlanStoreRequest;
use App\Http\Requests\Admin\UserPlan\UserPlanUpdateRequest;
use App\Http\Resources\Admin\UserPlan\UserPlanResource;
use App\Repositories\UserPlan\UserPlanRepositoryInterface;

class UserPlanController extends BaseController
{
    public function __construct(UserPlanRepositoryInterface $repository)
    {
        parent::__construct();
        $this->initService(
            repository: $repository,
            collectionName: 'UserPlan',
        );
        $this->withRelationships  = ['user:id,name,email', 'plan:id,name,desc,price'];
        $this->storeRequestClass  = UserPlanStoreRequest::class;
        $this->updateRequestClass = UserPlanUpdateRequest::class;
        $this->resourceClass      = UserPlanResource::class;
    }
}
