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
use Illuminate\Support\Facades\Log;

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

        $this->applyEnrollment($record);

        return $this->successResponse(new $this->showResourceClass($record), 'Record retrieved successfully');
    }

    // private function applyEnrollment($target): void
    // {
    //     $user = auth('api')->user();

    //     if (!$user) return;

    //     $enrolledIds = $user->enrollments()->pluck('course_id')->toArray();

    //     // لو collection (index)
    //     if ($target instanceof \Illuminate\Pagination\LengthAwarePaginator) {
    //         $target->getCollection()->transform(function ($course) use ($enrolledIds) {
    //             $course->setAttribute('is_enrolled', in_array($course->id, $enrolledIds));
    //             return $course;
    //         });
    //     }

    //     // لو single record (show)
    //     else {
    //         $target->setAttribute('is_enrolled', in_array($target->id, $enrolledIds));
    //     }
    // }

    private function applyEnrollment($target): void
    {
        $user = auth('api')->user();

        Log::channel('single')->info('=== applyEnrollment START ===', [
            'auth_user_id' => $user?->id,
            'auth_guard'   => 'api',
            'target_type'  => get_class($target),
        ]);

        if ($target instanceof \Illuminate\Pagination\LengthAwarePaginator) {
            if (!$user) {
                $target->getCollection()->transform(function ($course) {
                    $course->setAttribute('is_enrolled', false);
                    $course->setAttribute('enrollment_status', null);
                    return $course;
                });
                Log::channel('single')->info('No auth user — paginator set to false/null');
                return;
            }

            $enrollments = $user->enrollments()->pluck('status', 'course_id');

            Log::channel('single')->info('Paginator enrollments', [
                'user_id'     => $user->id,
                'enrollments' => $enrollments->toArray(),
            ]);

            $target->getCollection()->transform(function ($course) use ($enrollments) {
                $status = $enrollments->get($course->id);
                $course->setAttribute('is_enrolled', $status === 'approved');
                $course->setAttribute('enrollment_status', $status);

                Log::channel('single')->info("Course #{$course->id}", [
                    'status'      => $status,
                    'is_enrolled' => $status === 'approved',
                ]);

                return $course;
            });
        } else {
            if (!$user) {
                $target->setAttribute('is_enrolled', false);
                $target->setAttribute('enrollment_status', null);
                Log::channel('single')->info('No auth user — single record set to false/null');
                return;
            }

            $allEnrollments = $user->enrollments()->get(['course_id', 'status', 'user_id']);

            Log::channel('single')->info('Single record — all user enrollments', [
                'user_id'          => $user->id,
                'target_course_id' => $target->id,
                'all_enrollments'  => $allEnrollments->toArray(),
            ]);

            $enrollment = $user->enrollments()->where('course_id', $target->id)->first();

            Log::channel('single')->info('Single record — matched enrollment', [
                'enrollment' => $enrollment?->toArray(),
            ]);

            $target->setAttribute('is_enrolled', $enrollment?->status === 'approved');
            $target->setAttribute('enrollment_status', $enrollment?->status);

            Log::channel('single')->info('Single record — result', [
                'is_enrolled'       => $enrollment?->status === 'approved',
                'enrollment_status' => $enrollment?->status,
            ]);
        }

        Log::channel('single')->info('=== applyEnrollment END ===');
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

    protected function lookupColumn(): string
    {
        return 'slug';
    }
}
