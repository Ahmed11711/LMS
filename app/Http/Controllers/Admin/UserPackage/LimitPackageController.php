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
    public function getUsageSummary()
    {

        $features = DB::connection('tenant')
            ->table('tenant_feature_usage')
            ->where('is_enabled', true)
            ->get();


        return $this->successResponse($features, 'List Of My Use');
    }


    // if want get lable feature from central database, you can use this function
    // public function getUsageSummary()
    // {
    //     $labels = DB::connection('LMS_CENTER')
    //         ->table('features')
    //         ->get(['key', 'title', 'label'])
    //         ->keyBy('key');

    //     $features = DB::connection('tenant')
    //         ->table('tenant_feature_usage')
    //         ->where('is_enabled', true)
    //         ->get()
    //         ->map(function ($feature) use ($labels) {
    //             $central = $labels->get($feature->feature_slug);

    //             // label ← title ← slug متحول لشكل مقروء
    //             $feature->label = $central?->label
    //                 ?: $central?->title
    //                 ?: Str::headline($feature->feature_slug);

    //             return $feature;
    //         });

    //     return $this->successResponse($features, 'List Of My Use');
    // }
}
