<?php

namespace App\Http\Controllers\Admin\Me;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Me\MeResource;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class MeController extends Controller
{
    use ApiResponseTrait;
    public function me(Request $request)
    {
        $user = $request->user();
        return $this->successResponse(new MeResource($user), 'Me data retrieved successfully');
    }
}
