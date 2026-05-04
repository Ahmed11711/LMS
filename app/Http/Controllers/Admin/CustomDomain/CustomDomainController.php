<?php

namespace App\Http\Controllers\Admin\CustomDomain;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CustomDomain\CustomDomainRequest;
use App\Services\TenantService\DomainService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class CustomDomainController extends Controller
{
    use ApiResponseTrait;
    public function __construct(
        public DomainService $domainService
    ) {}

    public function setup(CustomDomainRequest $request)
    {
        $request->validated();
        $result = $this->domainService->setupDomain($request->domain);

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message']
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => $result['message']
        ], 200);
    }
}
