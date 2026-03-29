<?php

namespace App\Http\Controllers\Admin\Chapter;

use App\Repositories\Chapter\ChapterRepositoryInterface;
use App\Http\Controllers\BaseController\BaseController;
use App\Http\Requests\Admin\Chapter\ChapterStoreRequest;
use App\Http\Requests\Admin\Chapter\ChapterUpdateRequest;
use App\Http\Resources\Admin\Chapter\ChapterResource;

class ChapterController extends BaseController
{
    public function __construct(ChapterRepositoryInterface $repository)
    {
        parent::__construct();

        $this->initService(
            repository: $repository,
            collectionName: 'Chapter'
        );

        $this->storeRequestClass = ChapterStoreRequest::class;
        $this->updateRequestClass = ChapterUpdateRequest::class;
        $this->resourceClass = ChapterResource::class;
    }
}
