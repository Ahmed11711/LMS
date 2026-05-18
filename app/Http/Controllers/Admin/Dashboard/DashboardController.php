<?php

namespace App\Http\Controllers\Admin\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\UserBalance;

class DashboardController extends Controller
{
    public function index()
    {
        $userId = auth()->id();
        return  $userBanlnce = UserBalance::where('user_id', $userId)->first();
    }
}
