<?php

namespace App\Http\Controllers\User\Course;

use App\Http\Controllers\BaseController\BaseController;
use App\Repositories\Course\CourseRepositoryInterface;
use App\Http\Resources\User\Course\ApiCoursResource;
use App\Http\Resources\User\Course\ShowCoursResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Pipeline\Pipeline;
use App\QueryFilters\ColumnFilter;
use App\QueryFilters\Search;
use App\QueryFilters\SelectFields;
use App\QueryFilters\SortBy;

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

    public function index(Request $request): JsonResponse
    {
        try {
            $query = $this->repository->query()->with($this->getIndexRelationships());

            $data = app(Pipeline::class)
                ->send($query)
                ->through([
                    Search::class,
                    ColumnFilter::class,
                    SelectFields::class,
                    SortBy::class,
                ])
                ->thenReturn()
                ->latest()
                ->paginate($request->input('per_page', 10));

            $user = auth('api')->user();

            if ($user) {
                $enrolledIds = $user->enrollments()->pluck('course_id')->toArray();

                $data->getCollection()->transform(function ($course) use ($enrolledIds) {
                    $course->setAttribute('is_enrolled', in_array($course->id, $enrolledIds));
                    return $course;
                });
            }

            $data = $this->resourceClass::collection($data);

            return $this->successResponsePaginate($data, "Data retrieved via Pipeline");
        } catch (\Throwable $e) {
            return $this->errorResponse("Failed to fetch data", 500);
        }
    }

    public function show($id): JsonResponse
    {
        $record = $this->repository->query()
            ->with($this->getShowRelationships())
            ->where($this->lookupColumn(), $id)
            ->first();

        if (!$record) {
            return $this->errorResponse("Record not found", 404);
        }

        $this->applyEnrollment($record); // ✅

        return $this->successResponse(new $this->showResourceClass($record), 'Record retrieved successfully');
    }

    // ✅ method مشتركة بين index و show
    private function applyEnrollment($target): void
    {
        $user = auth('api')->user();

        if (!$user) return;

        $enrolledIds = $user->enrollments()->pluck('course_id')->toArray();

        // لو collection (index)
        if ($target instanceof \Illuminate\Pagination\LengthAwarePaginator) {
            $target->getCollection()->transform(function ($course) use ($enrolledIds) {
                $course->setAttribute('is_enrolled', in_array($course->id, $enrolledIds));
                return $course;
            });
        }

        // لو single record (show)
        else {
            $target->setAttribute('is_enrolled', in_array($target->id, $enrolledIds));
        }
    }

    protected function getShowRelationships(): array
    {
        return [
            'chapters.lessons',
            'infos',
            'category',
            'user:id,name,profile_image'
        ];
    }

    protected function lookupColumn(): string
    {
        return 'slug';
    }
}
