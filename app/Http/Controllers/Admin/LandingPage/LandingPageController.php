<?php

namespace App\Http\Controllers\Admin\LandingPage;

use App\Repositories\LandingPage\LandingPageRepositoryInterface;
use App\Http\Controllers\BaseController\BaseController;
use App\Http\Requests\Admin\LandingPage\LandingPageStoreRequest;
use App\Http\Requests\Admin\LandingPage\LandingPageUpdateRequest;
use App\Http\Resources\Admin\LandingPage\LandingPageResource;

class LandingPageController extends BaseController
{
    public function __construct(LandingPageRepositoryInterface $repository)
    {
        parent::__construct();

        $this->initService(
            repository: $repository,
            collectionName: 'LandingPage'
        );

        $this->storeRequestClass = LandingPageStoreRequest::class;
        $this->updateRequestClass = LandingPageUpdateRequest::class;
        $this->resourceClass = LandingPageResource::class;
    }
}
