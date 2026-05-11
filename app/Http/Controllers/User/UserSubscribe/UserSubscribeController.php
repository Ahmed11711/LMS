<?php

namespace App\Http\Controllers\User\UserSubscribe;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\UserSubscribe\StoreUserSubscribeRequest;
use App\Traits\ApiResponseTrait;
use App\Services\Payment\UserSubscribeService;

class UserSubscribeController extends Controller
{
  use ApiResponseTrait;

  public function __construct(
    private UserSubscribeService $service
  ) {}

  public function store(StoreUserSubscribeRequest $request)
  {
    $user =  $request->get('tenant_user');
    $tenant = app('tenant');

    $result = $this->service->execute(
      userId: $request->get('user_id'),
      courseId: $request->validated('course_id'),
      customerContact: $user->email ?? $user->phone,
      tenantDomain: $tenant->domain,

    );

    if (!$result['success']) {
      return $this->errorResponse($result['message'], 422);
    }

    return $this->successResponse([
      'payment_url' => $result['payment_url'],
    ]);
  }
}
