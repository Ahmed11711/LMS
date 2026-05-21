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

            $this->applyEnrollment($data);

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

    private function applyEnrollment($target): void
    {
        $user = auth('api')->user();
        if (!$user) return;

        $enrolledData = $user->enrollments()->pluck('status', 'course_id');

        if ($target instanceof \Illuminate\Pagination\LengthAwarePaginator) {
            $target->getCollection()->transform(function ($course) use ($enrolledData) {
                $course->setAttribute('enrollment_status', $enrolledData->get($course->id));
                return $course;
            });
        } else {
            $target->setAttribute('enrollment_status', $enrolledData->get($target->id));
        }
    }
    protected function getShowRelationships(): array
    {
        return [
            'chapters.lessons',
            'infos',
            'category',
            'user:id,name,profile_image',
            'courseReceiverAccounts.instructorReceiverAccount.receiverAccount',

        ];
    }

    protected function getIndexRelationships(): array
    {
        return [
            'userSubscribes' => function ($query) {
                $query->where('user_id', auth('api')->id());
            }
        ];
    }
    protected function lookupColumn(): string
    {
        return 'slug';
    }
}
