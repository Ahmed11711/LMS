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

        $features = DB::connection('tenant')
            ->table('tenant_feature_usage')

            ->where('is_enabled', true)
            ->get();


        return $this->successResponse($features, 'List Of My Usageing');
    }
}
