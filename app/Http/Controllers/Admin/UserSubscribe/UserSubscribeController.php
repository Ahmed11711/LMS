<?php

namespace App\Http\Controllers\Admin\UserSubscribe;

use App\Repositories\UserSubscribe\UserSubscribeRepositoryInterface;
use App\Http\Controllers\BaseController\BaseController;
use App\Http\Requests\Admin\UserSubscribe\UserSubscribeStoreRequest;
use App\Http\Requests\Admin\UserSubscribe\UserSubscribeUpdateRequest;
use App\Http\Resources\Admin\UserSubscribe\UserSubscribeResource;
use App\Models\Course;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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

        // دور على أي صف موجود أصلاً (مش بس active) لأن الـ unique constraint
        // شغالة على (user_id, course_id) بغض النظر عن status
        $existingSubscription = $this->repository->query()
            ->where('user_id', $userId)
            ->where('course_id', $courseId)
            ->first();

        // مفيش أي اشتراك قبل كده خالص -> إنشاء عادي
        if (!$existingSubscription) {
            return parent::store($request);
        }

        // هل الاشتراك الموجود لسه active وسريان؟
        $isCurrentlyActive = $existingSubscription->status === 'active'
            && ($existingSubscription->ends_at === null || $existingSubscription->ends_at > now());

        $renewalToken = $request->input('renewal_token');

        // لو الاشتراك لسه سريان وactive فعلاً، لازم تأكيد تجديد
        if ($isCurrentlyActive) {
            if (!$renewalToken || !$this->isValidRenewalToken($renewalToken, $userId, $courseId)) {
                return $this->errorResponse(
                    'هذا اليوزر مشترك بالفعل في هذا الكورس. لو عايز تجدد الاشتراك، ابعت نفس الطلب مع renewal_token اللي هنبعتهولك.',
                    409,
                    ['renewal_token' => $this->generateRenewalToken($userId, $courseId)]
                );
            }
        }

        // سواء كان منتهي أو نحتاج تجديد مؤكد -> نعمل update على نفس الصف
        // (مينفعش insert تاني بسبب الـ unique constraint)
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
    /**
     * When status is changed to 'active' via a normal update (not renewal),
     * auto-calculate ends_at based on the course's access settings.
     */
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
