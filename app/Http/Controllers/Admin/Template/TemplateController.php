<?php

namespace App\Http\Controllers\Admin\Template;

use App\Repositories\Template\TemplateRepositoryInterface;
use App\Http\Controllers\BaseController\BaseController;
use App\Http\Requests\Admin\Template\TemplateStoreRequest;
use App\Http\Requests\Admin\Template\TemplateUpdateRequest;
use App\Http\Resources\Admin\Template\TemplateResource;

class TemplateController extends BaseController
{
    public function __construct(TemplateRepositoryInterface $repository)
    {
        parent::__construct();

        $this->initService(
            repository: $repository,
            collectionName: 'Template'
        );

        $this->storeRequestClass = TemplateStoreRequest::class;
        $this->updateRequestClass = TemplateUpdateRequest::class;
        $this->resourceClass = TemplateResource::class;
    }
}
