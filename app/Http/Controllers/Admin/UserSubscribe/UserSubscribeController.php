<?php

namespace App\Http\Controllers\Admin\UserSubscribe;

use App\Repositories\UserSubscribe\UserSubscribeRepositoryInterface;
use App\Http\Controllers\BaseController\BaseController;
use App\Http\Requests\Admin\UserSubscribe\UserSubscribeStoreRequest;
use App\Http\Requests\Admin\UserSubscribe\UserSubscribeUpdateRequest;
use App\Http\Resources\Admin\UserSubscribe\UserSubscribeResource;
use App\Models\Course;
use App\QueryFilters\ColumnFilter;
use App\QueryFilters\Search;
use App\QueryFilters\SelectFields;
use App\QueryFilters\SortBy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;

class UserSubscribeController extends BaseController
{
    public function __construct(UserSubscribeRepositoryInterface $repository)
    {
        parent::__construct();

        $this->initService(
            repository: $repository,
            collectionName: 'UserSubscribe'
        );

        $this->storeRequestClass  = UserSubscribeStoreRequest::class;
        $this->updateRequestClass = UserSubscribeUpdateRequest::class;
        $this->resourceClass      = UserSubscribeResource::class;
        $this->withRelationships  = ['course:id,title', 'user:id,name,email'];
    }

    /**
    
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'period' => ['nullable', 'in:today,yesterday,week,month,year'],
            'date'   => ['nullable', 'date'],
            'from'   => ['nullable', 'date'],
            'to'     => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        try {
            $query = $this->repository->query()->with($this->getIndexRelationships());
            $query = $this->applyScoping($query);
            $query = $this->applyDateFilter($query, $request);

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

            if (class_exists($this->resourceClass)) {
                $data = $this->resourceClass::collection($data);
            }

            return $this->successResponsePaginate($data, "Data retrieved via Pipeline");
        } catch (\Throwable $e) {
            Log::error("Pipeline Error: " . $e->getMessage());
            return $this->errorResponse("Failed to fetch data", 500);
        }
    }

    /**
     */
    private function applyDateFilter($query, Request $request)
    {
        if ($request->filled('period')) {
            [$start, $end] = $this->resolvePeriodRange($request->input('period'));
            return $query->whereBetween('created_at', [$start, $end]);
        }

        if ($request->filled('date')) {
            $date = Carbon::parse($request->input('date'));
            return $query->whereDate('created_at', $date);
        }

        if ($request->filled('from') && $request->filled('to')) {
            $from = Carbon::parse($request->input('from'))->startOfDay();
            $to   = Carbon::parse($request->input('to'))->endOfDay();
            return $query->whereBetween('created_at', [$from, $to]);
        }

        return $query;
    }

    private function resolvePeriodRange(string $period): array
    {
        $now = Carbon::now();

        return match ($period) {
            'today'     => [$now->copy()->startOfDay(), $now->copy()->endOfDay()],
            'yesterday' => [$now->copy()->subDay()->startOfDay(), $now->copy()->subDay()->endOfDay()],
            'week'      => [$now->copy()->subDays(6)->startOfDay(), $now->copy()->endOfDay()],
            'month'     => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
            'year'      => [$now->copy()->startOfYear(), $now->copy()->endOfYear()],
            default     => [$now->copy()->startOfDay(), $now->copy()->endOfDay()],
        };
    }

    /**
     * Override store to handle the "already actively subscribed" case
     * and the renewal confirmation flow.
     */
    protected function beforeStore(array $data, Request $request): array
    {
        if (($data['status'] ?? null) === 'active') {
            $course = Course::find($data['course_id']);
            $startsAt = $data['starts_at'] ?? now();

            $data['starts_at'] = $startsAt;
            $data['ends_at']   = $this->calculateEndsAt($course, $startsAt);
        }

        return $data;
    }

    public function store(Request $request): JsonResponse
    {
        $validated = app($this->storeRequestClass)->validated();

        $userId   = (int) $validated['user_id'];
        $courseId = (int) $validated['course_id'];

        $existingSubscription = $this->repository->query()
            ->where('user_id', $userId)
            ->where('course_id', $courseId)
            ->first();

        if (!$existingSubscription) {
            return parent::store($request);
        }

        $isCurrentlyActive = $existingSubscription->status === 'active'
            && ($existingSubscription->ends_at === null || $existingSubscription->ends_at > now());

        $renewalToken = $request->input('renewal_token');

        if ($isCurrentlyActive) {
            if (!$renewalToken || !$this->isValidRenewalToken($renewalToken, $userId, $courseId)) {
                return $this->errorResponse(
                    'هذا اليوزر مشترك بالفعل في هذا الكورس. لو عايز تجدد الاشتراك، ابعت نفس الطلب مع renewal_token اللي هنبعتهولك.',
                    409,
                    ['renewal_token' => $this->generateRenewalToken($userId, $courseId)]
                );
            }
        }

        try {
            DB::beginTransaction();

            $course   = Course::find($courseId);
            $startsAt = $validated['starts_at'] ?? now();

            $existingSubscription->update([
                'status'    => $validated['status'] ?? 'active',
                'starts_at' => $startsAt,
                'ends_at'   => $this->calculateEndsAt($course, $startsAt),
            ]);

            DB::commit();

            $existingSubscription->load($this->withRelationships);

            return $this->successResponse(
                new $this->resourceClass($existingSubscription),
                'تم تجديد الاشتراك بنجاح',
                200
            );
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Error renewing UserSubscribe: " . $e->getMessage());
            return $this->errorResponse('فشل تجديد الاشتراك', 500);
        }
    }

    protected function beforeUpdate(array $data, $existingRecord, Request $request): array
    {
        if (
            isset($data['status']) &&
            $data['status'] === 'active' &&
            $existingRecord->status !== 'active'
        ) {
            $startsAt = $data['starts_at'] ?? $existingRecord->starts_at ?? now();
            $data['starts_at'] = $startsAt;
            $data['ends_at']   = $this->calculateEndsAt($existingRecord->course, $startsAt);
        }

        return $data;
    }

    // ----------------------------------------
    // Private Helpers
    // ----------------------------------------

    private function calculateEndsAt(?Course $course, $startsAt = null): ?Carbon
    {
        if (!$course) {
            return null;
        }

        $startsAt = $startsAt ? Carbon::parse($startsAt) : now();

        return match ($course->access_duration_type) {
            'days'       => $startsAt->copy()->addDays((int) $course->access_days),
            'until_date' => $course->access_until_date ? Carbon::parse($course->access_until_date) : null,
            default      => null, // lifetime
        };
    }

    private function generateRenewalToken(int $userId, int $courseId): string
    {
        return Crypt::encryptString(json_encode([
            'user_id'    => $userId,
            'course_id'  => $courseId,
            'expires_at' => now()->addMinutes(10)->timestamp,
        ]));
    }

    private function isValidRenewalToken(string $token, int $userId, int $courseId): bool
    {
        try {
            $payload = json_decode(Crypt::decryptString($token), true);
        } catch (\Throwable $e) {
            return false;
        }

        if (!$payload || !isset($payload['user_id'], $payload['course_id'], $payload['expires_at'])) {
            return false;
        }

        return (int) $payload['user_id'] === $userId
            && (int) $payload['course_id'] === $courseId
            && now()->timestamp <= (int) $payload['expires_at'];
    }
}
