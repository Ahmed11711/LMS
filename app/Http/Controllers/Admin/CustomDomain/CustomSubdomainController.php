<?php

namespace App\Http\Controllers\Admin\CustomDomain;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CustomDomain\CustomDomainRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CustomSubdomainController extends Controller
{
    public function setup(CustomDomainRequest $request)
    {
        $request->validated();

        $domain    = strtolower(trim($request->domain));
        $tenant    = app('tenant');
        $tenantId  = $tenant->id;
        $oldDomain = $tenant->domain;

        if ($this->isProtectedDomain($domain)) {
            return response()->json([
                'success' => false,
                'message' => "This domain is protected and cannot be used."
            ], 422);
        }

        $exists = DB::connection('LMS_CENTER')
            ->table('tenants')
            ->where('domain', $domain)
            ->where('id', '!=', $tenantId)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => "This domain is already taken by another account. Please choose a different one."
            ], 422);
        }

        try {
            DB::connection('LMS_CENTER')
                ->table('tenants')
                ->where('id', $tenantId)
                ->update([
                    'domain'     => $domain,
                    'updated_at' => now(),
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Domain updated successfully.',
                'data' => [
                    'old_domain' => $oldDomain,
                    'new_domain' => $domain,
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error("Failed to update tenant domain: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update domain. Please try again later.',
            ], 500);
        }
    }
}
