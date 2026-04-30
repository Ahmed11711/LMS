<?php

namespace App\Http\Controllers\User\UserSubscribe;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\UserSubscribe\StoreUserSubscribeRequest;
use App\Repositories\UserSubscribe\UserSubscribeRepository;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class UserSubscribeController extends Controller
{
    use ApiResponseTrait;
    public function __construct(public UserSubscribeRepository $userRepo)
    {
    }
    public function store(StoreUserSubscribeRequest $request)
    {
        $data = $request->validated();
        $data = array_merge($request->validated(), [
                'user_id' => $request->get('user_id') 
        ]);
        $subscription = $this->userRepo->create($data);
        return $this->successResponse($subscription);
    }
}
