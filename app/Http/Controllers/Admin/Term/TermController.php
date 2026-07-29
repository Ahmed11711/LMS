<?php

namespace App\Http\Controllers\Admin\Term;

use App\Repositories\Term\TermRepositoryInterface;
use App\Http\Controllers\BaseController\BaseController;
use App\Http\Requests\Admin\Term\TermStoreRequest;
use App\Http\Requests\Admin\Term\TermUpdateRequest;
use App\Http\Resources\Admin\Term\TermResource;

class TermController extends BaseController
{
    public function __construct(TermRepositoryInterface $repository)
    {
        parent::__construct();

        $this->initService(
            repository: $repository,
            collectionName: 'Term'
        );

        $this->storeRequestClass = TermStoreRequest::class;
        $this->updateRequestClass = TermUpdateRequest::class;
        $this->resourceClass = TermResource::class;
    }
}
