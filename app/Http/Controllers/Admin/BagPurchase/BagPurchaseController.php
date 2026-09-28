<?php

namespace App\Http\Controllers\Admin\BagPurchase;

use App\Repositories\BagPurchase\BagPurchaseRepositoryInterface;
use App\Http\Controllers\BaseController\BaseController;
use App\Http\Requests\Admin\BagPurchase\BagPurchaseStoreRequest;
use App\Http\Requests\Admin\BagPurchase\BagPurchaseUpdateRequest;
use App\Http\Resources\Admin\BagPurchase\BagPurchaseResource;
use App\Models\UserBagDownload;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BagPurchaseController extends BaseController
{
    public function __construct(BagPurchaseRepositoryInterface $repository)
    {
        parent::__construct();

        $this->initService(
            repository: $repository,
            collectionName: 'BagPurchase'
        );

        $this->storeRequestClass = BagPurchaseStoreRequest::class;
        $this->updateRequestClass = BagPurchaseUpdateRequest::class;
        $this->resourceClass = BagPurchaseResource::class;

        $this->withRelationships = ['bag', 'user'];
    }

    protected function getIndexRelationships(): array
    {
        return ['bag', 'user'];
    }

    /**
     * فلترة الـ query الأساسي حسب دور المستخدم.
     */
    protected function applyIndexFilters($query, Request $request)
    {
        $authUser = auth('api')->user();

        if ($authUser->role !== 'admin') {
            $query->whereHas('bag', function ($q) use ($authUser) {
                $q->where('user_id', $authUser->id);
            });
        }

        return $query;
    }

    /**
     * كروت الإحصائيات: طلبات في الانتظار / مقبولة ومفعلة / إجمالي الطلبات / إجمالي التحميلات
     */
    // public function stats(): JsonResponse
    // {
    //     $authUser = auth('api')->user();

    //     $baseQuery = $this->repository->query();

    //     if ($authUser->role !== 'admin') {
    //         $baseQuery->whereHas('bag', function ($q) use ($authUser) {
    //             $q->where('user_id', $authUser->id);
    //         });
    //     }

    //     $pendingReview = (clone $baseQuery)
    //         ->where('status', 'pending')
    //         ->count();

    //     $acceptedActive = (clone $baseQuery)
    //         ->whereIn('status', ['approved', 'accepted'])
    //         ->count();

    //     $totalRequests = (clone $baseQuery)->count();

    //     // ===== عدد التحميلات الكلي =====
    //     $downloadsQuery = UserBagDownload::query();

    //     if ($authUser->role !== 'admin') {
    //         $downloadsQuery->whereHas('bag', function ($q) use ($authUser) {
    //             $q->where('user_id', $authUser->id);
    //         });
    //     }

    //     $totalDownloads = $downloadsQuery->count();

    //     return $this->successResponse([
    //         'pending_review'  => $pendingReview,
    //         'accepted_active' => $acceptedActive,
    //         'total_requests'  => $totalRequests,
    //         'total_downloads' => $totalDownloads,
    //     ], 'Stats retrieved successfully');
    // }

    public function stats(Request $request): JsonResponse
    {
        $authUser = auth('api')->user();

        $bagId = $request->input('bag_id'); // أو 'bgs_id' لو ده الاسم اللي بتبعته

        $baseQuery = $this->repository->query();

        if ($authUser->role !== 'admin') {
            $baseQuery->whereHas('bag', function ($q) use ($authUser) {
                $q->where('user_id', $authUser->id);
            });
        }

        // فلتر الحقيبة
        if ($bagId) {
            $baseQuery->where('bag_id', $bagId);
        }

        $pendingReview = (clone $baseQuery)
            ->where('status', 'pending')
            ->count();

        $acceptedActive = (clone $baseQuery)
            ->whereIn('status', ['approved', 'accepted'])
            ->count();

        $totalRequests = (clone $baseQuery)->count();

        // ===== عدد التحميلات الكلي =====
        $downloadsQuery = UserBagDownload::query();

        if ($authUser->role !== 'admin') {
            $downloadsQuery->whereHas('bag', function ($q) use ($authUser) {
                $q->where('user_id', $authUser->id);
            });
        }

        // فلتر الحقيبة
        if ($bagId) {
            $downloadsQuery->where('bag_id', $bagId);
        }

        $totalDownloads = $downloadsQuery->count();

        return $this->successResponse([
            'pending_review'  => $pendingReview,
            'accepted_active' => $acceptedActive,
            'total_requests'  => $totalRequests,
            'total_downloads' => $totalDownloads,
        ], 'Stats retrieved successfully');
    }
}
