<?php

namespace App\Http\Controllers\Instructor\Course;

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

    protected function getShowRelationships(): array
    {
        return  [
            'chapters.lessons',
            'infos',
        ];
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

        $data = $this->sanitizeAccessDuration($data);

        return $data;
    }

    protected function afterStore($record, Request $request): void
    {
        $this->syncInfos($record, $request);
    }
    protected function beforeUpdate(array $data, $existingRecord, Request $request): array
    {
        unset($data['infos']);

        $data = $this->sanitizeAccessDuration($data);

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

    /**
     * Ensure only the fields relevant to the selected access_duration_type
     * are persisted; nulls out the irrelevant ones to avoid stale/conflicting data.
     */
    private function sanitizeAccessDuration(array $data): array
    {
        if (!isset($data['access_duration_type'])) {
            return $data;
        }

        switch ($data['access_duration_type']) {
            case 'lifetime':
                $data['access_days']       = null;
                $data['access_until_date'] = null;
                break;

            case 'days':
                $data['access_until_date'] = null;
                break;

            case 'until_date':
                $data['access_days'] = null;
                break;
        }

        return $data;
    }
}
