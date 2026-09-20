<?php

namespace App\Http\Controllers\Admin\UserPackage;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LimitPackageController extends Controller
{
    use ApiResponseTrait;
    // public function getUsageSummary()
    // {

    //     $features = DB::connection('tenant')
    //         ->table('tenant_feature_usage')
    //         ->where('is_enabled', true)
    //         ->get();


    //     return $this->successResponse($features, 'List Of My Use');
    // }

    public function getUsageSummary()
    {
        $labels = DB::connection('central')
            ->table('features')
            ->pluck('name', 'slug');

        $features = DB::connection('tenant')
            ->table('tenant_feature_usage')
            ->where('is_enabled', true)
            ->get()
            ->map(function ($feature) use ($labels) {
                $feature->label = $labels[$feature->feature_slug]
                    ?? Str::headline($feature->feature_slug);

                return $feature;
            });
    }
}
