<?php

namespace App\Http\Controllers\Constructor\Course;

use App\Http\Controllers\BaseController\BaseController;
use App\Http\Requests\Admin\Course\CourseUpdateRequest;
use App\Http\Requests\Admin\Course\InstructorCourseStoreRequest;
use App\Http\Resources\Admin\Course\CourseResource;
use App\Repositories\Course\CourseRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CourseController extends BaseController
{
    public function __construct(CourseRepositoryInterface $repository)
    {
        parent::__construct();

        $this->initService(
            repository: $repository,
            collectionName: 'courses',
            fileFields: ['thumbnail'],
        );

        $this->storeRequestClass  = InstructorCourseStoreRequest::class;
        $this->updateRequestClass = CourseUpdateRequest::class;
        $this->resourceClass      = CourseResource::class;
    }

    protected function applyScoping($query)
    {
        return $query->where('user_id', auth('api')->id());
    }

    protected function beforeStore(array $data, Request $request): array
    {
        unset($data['infos']);
        $data['slug']    = Str::slug($data['title']) . '-' . Str::random(6);
        $data['user_id'] = $request->attributes->get('user_id');

        return $data;
    }

    protected function afterStore($record, Request $request): void
    {
        $this->syncInfos($record, $request);
    }

    protected function beforeUpdate(array $data, $existingRecord, Request $request): array
    {
        unset($data['infos']);

        return $data;
    }

    protected function afterUpdate($updatedRecord, $oldRecord, Request $request): void
    {
        $this->syncInfos($updatedRecord, $request);
    }

    // ----------------------------------------
    // Private Helpers
    // ----------------------------------------

    private function syncInfos($record, Request $request): void
    {
        if (!$request->has('infos')) {
            return;
        }

        $record->infos()->delete();

        if (!empty($request->input('infos'))) {
            $infos = collect($request->input('infos'))->map(fn($info, $index) => [
                'info_key'   => $info['key'],
                'info_value' => $info['value'],
                'order'      => $info['order'] ?? $index + 1,
            ]);

            $record->infos()->createMany($infos);
        }
    }
}