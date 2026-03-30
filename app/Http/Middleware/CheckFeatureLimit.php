<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckFeatureLimit
{
    public function handle(Request $request, Closure $next, $featureSlug, $fileInputName = null)
    {
        if (!$request->isMethod('post')) {
            return $next($request);
        }
        $feature = DB::connection('tenant')
            ->table('tenant_feature_usage')
            ->where('feature_slug', $featureSlug)
            ->where('status', 'active')
            ->first();

        if (!$feature) {
            return response()->json(['message' => 'Feature locked or not found'], 403);
        }

        if ($feature->type === 'boolean') {
            if ($feature->is_enabled == false || $feature->total_limit == 0) {
                return response()->json(['message' => 'This feature is locked or not available in your current plan.'], 403);
            }
            return $next($request);
        }

        // 2. for Unlimited  
        if ($feature->total_limit == -1) {
            return $next($request);
        }

        // 3. فحص المساحة (Storage)
        // if ($fileInputName && $request->hasFile($fileInputName)) {
        //     $fileSizeInMB = $request->file($fileInputName)->getSize() / (1024 * 1024);
        //     if (($feature->used_amount + $fileSizeInMB) > $feature->total_limit) {
        //         return response()->json([
        //             'message' => 'مساحة التخزين المتبقية غير كافية.',
        //             'available' => round($feature->total_limit - $feature->used_amount, 2) . ' MB'
        //         ], 403);
        //     }
        // }

        // 4. فحص العدد (Count)
        else {
            if ($feature->used_amount >= $feature->total_limit) {
                if ($feature->used_amount >= $feature->total_limit) {
                    return response()->json([
                        'message' => 'You have reached the maximum limit for this feature.',
                        'limit'   => (int) $feature->total_limit,
                        'current' => (int) $feature->used_amount
                    ], 403);
                }
            }
        }

        return $next($request);
    }
}
