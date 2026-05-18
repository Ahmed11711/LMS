<?php

namespace App\Http\Controllers\Admin\Course;

use Illuminate\Support\Str;
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

        $this->withRelationships  = ['chapters.lessons', 'infos'];
        $this->storeRequestClass  = CourseStoreRequest::class;
        $this->updateRequestClass = CourseUpdateRequest::class;
        $this->resourceClass      = CourseResource::class;
    }

    protected function beforeStore(array $data, Request $request): array
    {
        unset($data['infos']);
        $data['slug'] = Str::slug($data['title']) . '-' . Str::random(6);
        $data['status'] = "published";

        return $data;
    }

    protected function afterStore($record, Request $request): void
    {
        $this->syncInfos($record, $request);
    }

    protected function beforeUpdate(array $data, $existingRecord, Request $request): array
    {
        unset($data['infos']);

        if (isset($data['title'])) {
            $data['slug'] = Str::slug($data['title']) . '-' . Str::random(6);
        }

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
    protected function beforeDestroy($record): void
    {
        if ($record->subscribers()->exists()) {
            abort(422, 'Cannot delete course with active enrollments');
        }

        $record->chapters()->each(function ($chapter) {
            $chapter->lessons()->delete();
            $chapter->delete();
        });
    }
}
