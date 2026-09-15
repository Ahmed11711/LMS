<?php

namespace App\Http\Controllers\Admin\Me;

use \App\Traits\ApiResponseTrait;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    use ApiResponseTrait;
    public function index()
    {
        $user = auth('api')->user();
        return  $this->successResponse($user, 'User profile retrieved successfully');
    }
}
