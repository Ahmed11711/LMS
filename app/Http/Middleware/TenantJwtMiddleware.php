<?php

namespace App\Http\Middleware;

use App\Traits\ApiResponseTrait;
use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TenantJwtMiddleware
{
    use ApiResponseTrait;
    public function handle(Request $request, Closure $next)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return $this->errorResponse('User not found', 404);
            }

            $payload = JWTAuth::parseToken()->getPayload();
            $tokenTenantId = $payload->get('tenant_id');
            $currentTenantId = app('tenant')->id;

            if ($tokenTenantId != $currentTenantId) {
                return $this->errorResponse('Token does not match tenant', 401);
            }
        } catch (JWTException $e) {
            return $this->errorResponse('Token invalid or expired', 401);
        }

        $request->attributes->add(['tenant_user' => $user]);
        // Log::info('Tenant JWT verified', [
        //     'tenant_db' => DB::connection()->getDatabaseName(),
        //     'user_id' => $user->id,
        //     'user_email' => $user->email,
        //     'request_url' => $request->fullUrl(),
        //     'request_method' => $request->method()
        // ]);

        return $next($request);
    }
}
