<?php

namespace App\Http\Controllers\User\Profile;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class MeController extends Controller
{
    use ApiResponseTrait;
    public function me(Request $request)
    {
        $user = auth('api')->user();
        return $this->successResponse($user, 'Me data retrieved successfully');
    }
}
