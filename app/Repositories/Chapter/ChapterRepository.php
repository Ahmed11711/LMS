<?php

namespace App\Repositories\Chapter;

use App\Repositories\Chapter\ChapterRepositoryInterface;
use App\Repositories\BaseRepository\BaseRepository;
use App\Models\Chapter;

class ChapterRepository extends BaseRepository implements ChapterRepositoryInterface
{
    public function __construct(Chapter $model)
    {
        parent::__construct($model);
    }
}
