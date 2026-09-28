<?php

namespace App\Repositories\Video;

use App\Repositories\Video\VideoRepositoryInterface;
use App\Repositories\BaseRepository\BaseRepository;
use App\Models\Video;

class VideoRepository extends BaseRepository implements VideoRepositoryInterface
{
    public function __construct(Video $model)
    {
        parent::__construct($model);
    }
}
