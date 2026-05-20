<?php

namespace App\Http\Controllers\User\UserPlan;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\UserPlan\StoreUserPlanRequest;
use App\Services\UserPlan\UserPlanService;
use App\Traits\ApiResponseTrait;

class UserPlanController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private UserPlanService $service
    ) {}

    public function store(StoreUserPlanRequest $request)
    {
        $user   = $request->get('tenant_user');
        $tenant = app('tenant');

        $result = $this->service->execute(
            payment: false,
            userId: $request->get('user_id'),
            planId: $request->validated('plan_id'),
            customerContact: $user->email ?? $user->phone,
            tenantDomain: $tenant->domain,
            receipt: $request->file('receipt'),
        );

        if (!$result['success']) {
            return $this->errorResponse($result['message'], 422);
        }

        return $this->successResponse([
            'payment_url' => $result['payment_url'] ?? null,
            'message'     => $result['message'] ?? null,
        ]);
    }
}
