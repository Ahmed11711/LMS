<?php

namespace App\Http\Controllers\Admin\Course;

use App\Repositories\Course\CourseRepositoryInterface;
use App\Http\Controllers\BaseController\BaseController;
use App\Http\Requests\Admin\Course\CourseStoreRequest;
use App\Http\Requests\Admin\Course\CourseUpdateRequest;
use App\Http\Resources\Admin\Course\CourseResource;
use Illuminate\Http\Request; 
class CourseController extends BaseController
{
    public function __construct(CourseRepositoryInterface $repository)
    {
        parent::__construct();

        $this->initService(
            repository: $repository,
            collectionName: 'Course',
            fileFields: ['image']


        );
        $this->withRelationships = ['chapters.lessons'];
        $this->storeRequestClass = CourseStoreRequest::class;
        $this->updateRequestClass = CourseUpdateRequest::class;
        $this->resourceClass = CourseResource::class;
    }
    protected function beforeStore(array $data, Request $request): array
    {
        unset($data['infos']); 
        return $data;
    }

        protected function afterStore($record, Request $request): void
       {
        if ($request->has('infos')) {
            $infos = collect($request->input('infos'))->map(fn($info, $index) => [
                'key'   => $info['key'],
                'value' => $info['value'],
                'order' => $info['order'] ?? $index + 1,
             ]);

            $record->infos()->createMany($infos);
        }
    }


    
}
