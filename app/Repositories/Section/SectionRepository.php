<?php

namespace App\Repositories\Section;

use App\Repositories\Section\SectionRepositoryInterface;
use App\Repositories\BaseRepository\BaseRepository;
use App\Models\Section;

class SectionRepository extends BaseRepository implements SectionRepositoryInterface
{
    public function __construct(Section $model)
    {
        parent::__construct($model);
    }
}
