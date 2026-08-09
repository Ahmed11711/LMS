<?php

namespace App\Http\Controllers\User\UserSubscribe;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\UserSubscribe\StoreUserSubscribeRequest;
use App\Http\Resources\User\UserSubscribe\UserSubscribeResource;
use App\Traits\ApiResponseTrait;
use App\Services\Payment\UserSubscribeService;
use Illuminate\Http\Request;

class UserSubscribeController extends Controller
{
  use ApiResponseTrait;

  public function __construct(
    private UserSubscribeService $service
  ) {}

  public function index(Request $request)
  {
    $user =  $request->get('tenant_user');

    $subscribes = $this->service->getUserSubscribes($user->id);


    return $this->successResponse(UserSubscribeResource::collection($subscribes));
  }
  public function store(StoreUserSubscribeRequest $request)
  {
    $user = $request->get('tenant_user');
    $tenant = app('tenant');

    $result = $this->service->execute(
      payment: false,
      userId: $request->get('user_id'),
      courseId: $request->validated('course_id'),
      customerContact: $user->email ?? $user->phone,
      tenantDomain: $tenant->domain,
      receipt: $request->file('receipt'),
      receiverAccountId: $request->validated('receiver_account_id'),
    );

    if (!$result['success']) {
      return $this->errorResponse($result['message'], 422);
    }

    return $this->successResponse($result);
  }
}
