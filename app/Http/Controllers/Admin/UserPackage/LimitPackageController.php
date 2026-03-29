<?php

namespace App\Http\Controllers\Admin\UserPackage;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class LimitPackageController extends Controller
{
    use ApiResponseTrait;
    public function getUsageSummary()
    {
        return $features = DB::connection('tenant')
            ->table('tenant_feature_usage')

            ->where('is_enabled', true)
            ->get();


        return $features;

        if ($features->isEmpty()) {
            return response()->json([
                'status' => 'success',
                'data' => []
            ]);
        }

        $formattedData = $features->map(function ($item) {
            return [
                'feature'    => $item->feature_slug,
                'label'      => $item->display_name ?? str_replace('_', ' ', ucfirst($item->feature_key)),
                'limit'      => (int) $item->limit_count,
                'used'       => (int) $item->consumed,
                'remaining'  => max(0, $item->limit_count - $item->consumed),
                'percentage' => $item->limit_count > 0
                    ? round(($item->consumed / $item->limit_count) * 100, 2)
                    : 0
            ];
        });

        return response()->json([
            'status' => 'success',
            'data'   => $formattedData
        ]);
    }
}
