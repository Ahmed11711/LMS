<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SuperAdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::guard('central')->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized: Not logged in'], 401);
        }


        if ($user->role !== 'super_admin') {
            return response()->json(['message' => 'Forbidden: Not SuperAdmin'], 403);
        }

        return $next($request);
    }
}
