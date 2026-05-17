<?php

namespace App\Http\Controllers\Admin\UserBalance;

use App\Http\Controllers\Controller;
use App\Models\UserBalance;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class UserBalalnceController extends Controller
{
    use ApiResponseTrait;
    public function index()
    {
        $userId = auth()->id();
        $balances = UserBalance::where('user_id', $userId)->first();
        return $this->successResponse($balances, "M Balalnce");
    }
}
