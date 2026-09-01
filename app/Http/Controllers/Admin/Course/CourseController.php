<?php

namespace App\Http\Controllers\Admin\Course;

use Illuminate\Support\Str;
use App\Repositories\Course\CourseRepositoryInterface;
use App\Http\Controllers\BaseController\BaseController;
use App\Http\Requests\Admin\Course\CourseStoreRequest;
use App\Http\Requests\Admin\Course\CourseUpdateRequest;
use App\Http\Resources\Admin\Course\CourseResource;
use App\QueryFilters\ColumnFilter;
use App\QueryFilters\Search;
use App\QueryFilters\SelectFields;
use App\QueryFilters\SortBy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\Log;
use Override;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


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

        $this->storeRequestClass  = CourseStoreRequest::class;
        $this->updateRequestClass = CourseUpdateRequest::class;
        $this->resourceClass      = CourseResource::class;
        $this->withRelationships = ['category:id,name', 'user:id,name'];
    }

    #[Override]
    protected function getShowRelationships(): array
    {
        return [
            'chapters.lessons',
            'infos',
            'courseReceiverAccounts.instructorReceiverAccount.receiverAccount',
            'category:id,name',
            'user:id,name',
            'grade:id,name',
            'term:id,name',
            'subject:id,name',
            'academicYear:id,name',
        ];
    }

    // ----------------------------------------
    // Overridden index/show (with aggregates)
    // ----------------------------------------


    #[Override]
    public function index(Request $request): JsonResponse
    {
        try {
            $priceColumnExists = Schema::hasColumn('user_subscribes', 'price');

            $query = $this->repository->query()
                ->with($this->getIndexRelationships())
                ->withCount('activeSubscribers');

            if ($priceColumnExists) {
                $query->withSum(
                    ['activeSubscribers as total_sales'],
                    DB::raw("CAST(NULLIF(price, '') AS numeric)")
                );
            }

            $query = $this->applyScoping($query);

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

            // لو العمود مش موجود، نضيف total_sales = 0 يدويًا لكل عنصر
            if (!$priceColumnExists) {
                $data->getCollection()->transform(function ($item) {
                    $item->total_sales = 0;
                    return $item;
                });
            }

            $data = CourseResource::collection($data);

            return $this->successResponsePaginate($data, "Data retrieved via Pipeline");
        } catch (\Throwable $e) {
            Log::error("Pipeline Error: " . $e->getMessage());
            return $this->errorResponse("Failed to fetch data", 500);
        }
    }

    #[Override]
    public function show($id): JsonResponse
    {
        $priceColumnExists = Schema::hasColumn('user_subscribes', 'price');

        $query = $this->repository->query()
            ->with($this->getShowRelationships())
            ->withCount('activeSubscribers');

        if ($priceColumnExists) {
            $query->withSum(
                ['activeSubscribers as total_sales'],
                DB::raw("CAST(NULLIF(price, '') AS numeric)")
            );
        }

        $query = $this->applyScoping($query);

        $record = $query->where($this->lookupColumn(), $id)->first();

        if (!$record) {
            return $this->errorResponse("Record not found", 404);
        }

        if (!$priceColumnExists) {
            $record->total_sales = 0;
        }

        return $this->successResponse(new CourseResource($record), 'Record retrieved successfully');
    }
    // ----------------------------------------
    // Hooks
    // ----------------------------------------

    protected function beforeStore(array $data, Request $request): array
    {
        unset($data['infos']);
        unset($data['receiver_accounts']);
        $data['slug'] = Str::slug($data['title']) . '-' . Str::random(6);
        $data['status'] = "published";

        return $data;
    }

    protected function afterStore($record, Request $request): void
    {
        $this->syncInfos($record, $request);
        $this->syncReceiverAccounts($record, $request);
    }

    protected function beforeUpdate(array $data, $existingRecord, Request $request): array
    {
        unset($data['infos']);
        unset($data['receiver_accounts']);

        if (isset($data['title']) && $data['title'] !== $existingRecord->title) {
            $data['slug'] = Str::slug($data['title']) . '-' . Str::random(6);
        }

        return $data;
    }

    protected function afterUpdate($updatedRecord, $oldRecord, Request $request): void
    {
        $this->syncInfos($updatedRecord, $request);
        $this->syncReceiverAccounts($updatedRecord, $request);
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

    private function syncReceiverAccounts($record, Request $request): void
    {
        if (!$request->has('receiver_accounts')) {
            return;
        }

        $record->courseReceiverAccounts()->delete();

        if (!empty($request->input('receiver_accounts'))) {
            $accounts = collect($request->input('receiver_accounts'))->map(fn($id) => [
                'instructor_receiver_account_id' => $id,
            ]);

            $record->courseReceiverAccounts()->createMany($accounts);
        }
    }
}
