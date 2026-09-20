<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckFeatureLimit
{
    public function handle(Request $request, Closure $next, $featureSlug, $fileInputName = null)
    {
        $feature = DB::connection('tenant')
            ->table('tenant_feature_usage')
            ->where('feature_slug', $featureSlug)
            ->where('status', 'active')
            ->first();

        if (!$feature) {
            return response()->json([
                'code'    => 'FEATURE_NOT_FOUND',
                'message' => __('limits.not_found'),
            ], 403);
        }

        // 1. Boolean features
        if ($feature->type === 'boolean') {
            if ($feature->is_enabled == false || $feature->total_limit == 0) {
                return response()->json([
                    'code'    => 'FEATURE_LOCKED',
                    'message' => __('limits.locked'),
                ], 403);
            }
            return $next($request);
        }

        // 2. Unlimited features
        if ($feature->total_limit == -1) {
            return $next($request);
        }

        // 3. Storage check
        if ($fileInputName && $request->hasFile($fileInputName)) {
            $fileSizeInMB = $request->file($fileInputName)->getSize() / (1024 * 1024);

            if (($feature->used_amount + $fileSizeInMB) > $feature->total_limit) {
                $available = max(0, $feature->total_limit - $feature->used_amount);

                return response()->json([
                    'code'      => 'STORAGE_FULL',
                    'message'   => __('limits.storage_full'),
                    'available' => round($available, 2) . ' ' . __('limits.mb'),
                ], 403);
            }

            return $next($request);
        }

        // 4. Count check
        if ($feature->used_amount >= $feature->total_limit) {
            return response()->json([
                'code'    => 'FEATURE_LIMIT_REACHED',
                'message' => __('limits.limit_reached'),
                'limit'   => (int) $feature->total_limit,
                'current' => (int) $feature->used_amount,
            ], 403);
        }

        return $next($request);
    }
}
