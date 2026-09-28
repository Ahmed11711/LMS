<?php

namespace App\Http\Controllers\Admin\Video;

use App\Repositories\Video\VideoRepositoryInterface;
use App\Http\Controllers\BaseController\BaseController;
use App\Http\Requests\Admin\Video\VideoStoreRequest;
use App\Http\Requests\Admin\Video\VideoUpdateRequest;
use App\Http\Resources\Admin\Video\VideoResource;

class VideoController extends BaseController
{
    public function __construct(VideoRepositoryInterface $repository)
    {
        parent::__construct();

        $this->initService(
            repository: $repository,
            collectionName: 'Video',
            fileFields: ['file_size_mb']
        );

        $this->storeRequestClass = VideoStoreRequest::class;
        $this->updateRequestClass = VideoUpdateRequest::class;
        $this->resourceClass = VideoResource::class;
    }
}
