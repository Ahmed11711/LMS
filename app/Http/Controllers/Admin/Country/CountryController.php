<?php

namespace App\Http\Controllers\Admin\Country;

use App\Repositories\Country\CountryRepositoryInterface;
use App\Http\Controllers\BaseController\BaseController;
use App\Http\Requests\Admin\Country\CountryStoreRequest;
use App\Http\Requests\Admin\Country\CountryUpdateRequest;
use App\Http\Resources\Admin\Country\CountryResource;

class CountryController extends BaseController
{
    public function __construct(CountryRepositoryInterface $repository)
    {
        parent::__construct();

        $this->initService(
            repository: $repository,
            collectionName: 'Country'
        );

        $this->storeRequestClass = CountryStoreRequest::class;
        $this->updateRequestClass = CountryUpdateRequest::class;
        $this->resourceClass = CountryResource::class;
    }
}
