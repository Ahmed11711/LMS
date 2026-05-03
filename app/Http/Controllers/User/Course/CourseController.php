<?php

namespace App\Http\Controllers\User\Course;

use App\Http\Controllers\BaseController\BaseController;
use App\Repositories\Course\CourseRepositoryInterface;
use App\Http\Resources\Admin\Course\CourseResource;
use Illuminate\Http\Request;
use App\Http\Resources\User\Course\ApiCoursResource;
use App\Http\Resources\User\Course\ShowCoursResource;

class CourseController extends BaseController
{
    public function __construct(CourseRepositoryInterface $repository)
    {
        parent::__construct();

        $this->initService(
            repository: $repository,
            collectionName: 'Course',
        );
        $this->withRelationships = [];
        $this->resourceClass = ApiCoursResource::class;
        $this->showResourceClass = ShowCoursResource::class;
    }
    protected function getShowRelationships(): array
    {
        return [
            'chapters.lessons',
            'infos',
            'category',
            'user:id,name,image'
        ];
    }
    protected function lookupColumn(): string
    {
        return 'slug';
    }
}
