<?php

namespace App\Http\Controllers\Admin\OnlineSession;

use App\Repositories\OnlineSession\OnlineSessionRepositoryInterface;
use App\Http\Controllers\BaseController\BaseController;
use App\Http\Requests\Admin\OnlineSession\OnlineSessionStoreRequest;
use App\Http\Requests\Admin\OnlineSession\OnlineSessionUpdateRequest;
use App\Http\Resources\Admin\OnlineSession\OnlineSessionResource;

class OnlineSessionController extends BaseController
{
    public function __construct(OnlineSessionRepositoryInterface $repository)
    {
        parent::__construct();

        $this->initService(
            repository: $repository,
            collectionName: 'OnlineSession'
        );

        $this->storeRequestClass = OnlineSessionStoreRequest::class;
        $this->updateRequestClass = OnlineSessionUpdateRequest::class;
        $this->resourceClass = OnlineSessionResource::class;
    }
}
