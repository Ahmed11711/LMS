<?php

namespace App\Http\Controllers\Admin\CustomDomain;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CustomDomain\CustomDomainRequest;
use App\Jobs\SetupCustomDomainJob;
use App\Services\DomainService\DomainService;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class CustomDomainController extends Controller
{
    /** A "pending" request older than this is considered dead. */
    private const STALE_AFTER_MINUTES = 10;

    private const MAX_CHANGES_PER_DAY = 5;

    public function __construct(
        private DomainService $domainService
    ) {}

    // ------------------------------------------------------------
    // POST|PUT /custom-domain
    // ------------------------------------------------------------
    public function setup(CustomDomainRequest $request): JsonResponse
    {
        $domain   = $request->validated()['domain'];
        $tenantId = app('tenant')->id;

        // 1. Protected domain check
        if ($this->domainService->isProtectedDomain($domain)) {
            return $this->error("This domain is protected and cannot be used.", 422);
        }

        $tenant = $this->tenants()->where('id', $tenantId)->first();

        // 2. Already this tenant's active domain -> nothing to do
        if ($tenant && $tenant->domain === $domain) {
            return response()->json([
                'success' => true,
                'message' => "This domain is already configured.",
                'data'    => ['domain' => $domain, 'status' => 'active'],
            ]);
        }

        // 3. Cooldown: one successful change every N days
        $cooldownDays = (int) config('domain.change_cooldown_days', 60);

        if ($tenant && $tenant->domain_changed_at) {
            $nextAllowed = Carbon::parse($tenant->domain_changed_at)->addDays($cooldownDays);

            if ($nextAllowed->isFuture()) {
                return response()->json([
                    'success' => false,
                    'message' => "You can change your domain once every {$cooldownDays} days. Next change allowed on " . $nextAllowed->toDateString() . ".",
                    'data'    => ['next_change_allowed_at' => $nextAllowed->toIso8601String()],
                ], 429);
            }
        }

        // 4. Uniqueness (live domains + domains other tenants are setting up)
        $taken = $this->tenants()
            ->where('id', '!=', $tenantId)
            ->where(fn($q) => $q->where('domain', $domain)->orWhere('pending_domain', $domain))
            ->exists();

        if ($taken) {
            return $this->error("This domain is already taken. Please choose a different one.", 422);
        }

        // 5. Daily attempts limit per tenant (protects Let's Encrypt quotas)
        $limiterKey = "domain-change:{$tenantId}";
        if (RateLimiter::tooManyAttempts($limiterKey, self::MAX_CHANGES_PER_DAY)) {
            return $this->error(
                "Too many domain changes today. Try again in " . ceil(RateLimiter::availableIn($limiterKey) / 60) . " minutes.",
                429
            );
        }

        // 6. Atomically claim the tenant: only one change at a time + cooldown check
        try {
            $claimed = $this->tenants()
                ->where('id', $tenantId)
                ->where(function ($q) use ($cooldownDays) {
                    $q->whereNull('domain_changed_at')
                        ->orWhere('domain_changed_at', '<=', now()->subDays($cooldownDays));
                })
                ->where(function ($q) {
                    $q->where('domain_status', '!=', 'pending')
                        ->orWhereNull('domain_status')
                        ->orWhere('domain_requested_at', '<', now()->subMinutes(self::STALE_AFTER_MINUTES));
                })
                ->update([
                    'pending_domain'      => $domain,
                    'domain_status'       => 'pending',
                    'domain_error'        => null,
                    'domain_requested_at' => now(),
                ]);
        } catch (QueryException $e) {
            // Unique index on pending_domain: another tenant claimed it a millisecond earlier.
            if (in_array((string) $e->getCode(), ['23000', '23505'], true)) {
                return $this->error("This domain is already taken. Please choose a different one.", 422);
            }
            throw $e;
        }

        if ($claimed === 0) {
            return $this->error("A domain change is already in progress, or the change limit was reached.", 409);
        }

        RateLimiter::hit($limiterKey, 86400);

        // 7. Run the slow part (nginx + certbot) right after the response is sent.
        try {
            SetupCustomDomainJob::dispatch($tenantId, $domain)->afterResponse();
        } catch (\Throwable $e) {
            Log::error("Failed to start SetupCustomDomainJob: " . $e->getMessage());

            $this->tenants()->where('id', $tenantId)->update([
                'pending_domain' => null,
                'domain_status'  => 'failed',
                'domain_error'   => 'Could not start the domain setup. Please try again.',
            ]);

            return $this->error("Could not start the domain setup. Please try again.", 500);
        }

        Log::info("Domain setup queued", ['domain' => $domain, 'tenant_id' => $tenantId]);

        return response()->json([
            'success' => true,
            'message' => "Domain setup started. Check the status endpoint for progress.",
            'data'    => ['pending_domain' => $domain, 'status' => 'pending'],
        ], 202);
    }

    // ------------------------------------------------------------
    // GET /custom-domain/status
    // ------------------------------------------------------------
    public function status(): JsonResponse
    {
        $tenant = $this->tenants()->where('id', app('tenant')->id)->first();

        if (!$tenant) {
            return $this->error("Tenant not found.", 404);
        }

        $status = $tenant->domain_status ?: 'active';
        $error  = $tenant->domain_error;

        // Process died without reporting: don't leave the UI spinning forever.
        if (
            $status === 'pending'
            && $tenant->domain_requested_at
            && now()->diffInMinutes($tenant->domain_requested_at, true) >= self::STALE_AFTER_MINUTES
        ) {
            $status = 'failed';
            $error  = 'The request timed out. Please try again.';
        }

        $cooldownDays = (int) config('domain.change_cooldown_days', 60);
        $nextAllowed  = $tenant->domain_changed_at
            ? Carbon::parse($tenant->domain_changed_at)->addDays($cooldownDays)
            : null;
        $onCooldown = $nextAllowed && $nextAllowed->isFuture();

        return response()->json([
            'success' => true,
            'data'    => [
                'domain'                 => $tenant->domain,
                'pending_domain'         => $tenant->pending_domain,
                'status'                 => $status, // active | pending | failed
                'error'                  => $status === 'failed' ? $error : null,
                'can_change'             => $status !== 'pending' && !$onCooldown,
                'next_change_allowed_at' => $onCooldown ? $nextAllowed->toIso8601String() : null,
            ],
        ]);
    }

    // ------------------------------------------------------------

    private function tenants()
    {
        return DB::connection('LMS_CENTER')->table('tenants');
    }

    private function error(string $message, int $code): JsonResponse
    {
        return response()->json(['success' => false, 'message' => $message], $code);
    }
}
