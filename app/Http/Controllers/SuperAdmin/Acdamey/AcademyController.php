<?php

namespace App\Http\Controllers\SuperAdmin\Acdamey;

use App\Models\Central\User;
use App\Models\Central\Package;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Pipeline\Pipeline;
use App\QueryFilters\ColumnFilter;
use App\QueryFilters\Search;
use App\QueryFilters\SelectFields;
use App\QueryFilters\SortBy;
use App\Http\Controllers\BaseController\BaseController;
use App\Http\Resources\SuperAdmin\AcademyResource;
use App\Http\Requests\SuperAdmin\Academy\AcademyUpdateRequest;

class AcademyController extends BaseController
{
    public function __construct()
    {
        parent::__construct();

        $this->repository = new class {
            public function query()
            {
                return User::query();
            }
        };

        $this->collectionName = 'Academy';
        $this->resourceClass = AcademyResource::class;
        $this->withRelationships = ['tenant', 'activePackage.package'];

        $this->updateRequestClass = AcademyUpdateRequest::class;
    }

    protected function applyScoping($query)
    {
        $query = parent::applyScoping($query);

        $query = $query->where('role', 'academy');
        $query = $this->applyDateFilter($query, request());
        $query = $this->applyPackageFilter($query, request());

        return $query;
    }

    /**
     * Override بيحافظ على نفس الـ Pipeline بتاع BaseController بالظبط،
     * وبيضيف بس stats (active/inactive/total) جوا نفس الـ response.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = $this->repository->query()->with($this->getIndexRelationships());
            $query = $this->applyScoping($query);

            // stats محسوبة على نفس الـ scoping (نفس الفلاتر: date/package/role)
            // لكن قبل ما الـ Pipeline يطبق Search/ColumnFilter عشان الكروت تفضل ثابتة
            // مع فلاتر التاريخ والباقة بس، مش متأثرة بالبحث النصي في الجدول
            $statsBase = clone $query;
            $stats = [
                'active'   => (clone $statsBase)->where('is_active', true)->count(),
                'inactive' => (clone $statsBase)->where('is_active', false)->count(),
                'total'    => (clone $statsBase)->count(),
            ];

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
                $data = $this->resourceClass::collection($data)
                    ->additional(['stats' => $stats]);
            }

            return $this->successResponsePaginate($data, "Data retrieved via Pipeline");
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Pipeline Error: " . $e->getMessage());
            return $this->errorResponse("Failed to fetch data", 500);
        }
    }

    protected function applyDateFilter($query, Request $request)
    {
        if ($request->filled('date_from') || $request->filled('date_to')) {
            if ($request->filled('date_from')) {
                $query->where('created_at', '>=', Carbon::parse($request->date_from)->startOfDay());
            }
            if ($request->filled('date_to')) {
                $query->where('created_at', '<=', Carbon::parse($request->date_to)->endOfDay());
            }
            return $query;
        }

        return match ($request->get('period')) {
            'today', 'day' => $query->whereDate('created_at', Carbon::today()),
            'yesterday'    => $query->whereDate('created_at', Carbon::yesterday()),
            'week'         => $query->where('created_at', '>=', Carbon::now()->subWeek()),
            'month'        => $query->where('created_at', '>=', Carbon::now()->subMonth()),
            'year'         => $query->where('created_at', '>=', Carbon::now()->subYear()),
            default        => $query,
        };
    }

    protected function applyPackageFilter($query, Request $request)
    {
        if (!$request->filled('package_id')) {
            return $query;
        }

        if ($request->package_id === 'none') {
            return $query->whereDoesntHave('packages', function ($q) {
                $q->where('status', 'active');
            });
        }

        return $query->whereHas('packages', function ($q) use ($request) {
            $q->where('package_id', $request->package_id)
                ->where('status', 'active');
        });
    }
    public function stats(Request $request): JsonResponse
    {
        $base = User::query()->where('role', 'academy');
        $base = $this->applyDateFilter($base, $request);
        $base = $this->applyPackageFilter($base, $request);

        return $this->successResponse([
            'active'   => (clone $base)->where('is_active', true)->count(),
            'inactive' => (clone $base)->where('is_active', false)->count(),
            'total'    => (clone $base)->count(),
        ], 'Stats retrieved successfully');
    }

    public function packagesList()
    {
        return response()->json(
            Package::select('id', 'name')->get()
        );
    }
}
