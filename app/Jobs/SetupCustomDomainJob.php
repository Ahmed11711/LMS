<?php

namespace App\Jobs;

use App\Services\DomainService\DomainService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SetupCustomDomainJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;
    public int $timeout = 420;

    public function __construct(
        public int|string $tenantId,
        public string $domain,
    ) {}

    public function handle(DomainService $service): void
    {
        // Runs after the HTTP response (afterResponse), so PHP's time limit must be lifted.
        set_time_limit(0);

        $tenant = $this->tenants()->where('id', $this->tenantId)->first();

        // Request was superseded or cancelled.
        if (!$tenant || $tenant->pending_domain !== $this->domain) {
            Log::warning("SetupCustomDomainJob skipped: request no longer current", [
                'tenant_id' => $this->tenantId,
                'domain'    => $this->domain,
            ]);
            return;
        }

        $oldDomain = $tenant->domain;

        // 1. Set up the NEW domain while the old one keeps serving traffic.
        $result = $service->setupDomain($this->domain);

        if (!$result['success']) {
            $this->markFailed($result['message']);
            return;
        }

        // 2. Switch the tenant to the new domain and start the cooldown.
        try {
            $this->tenants()->where('id', $this->tenantId)->update([
                'domain'            => $this->domain,
                'pending_domain'    => null,
                'domain_status'     => 'active',
                'domain_error'      => null,
                'domain_changed_at' => now(),
                'updated_at'        => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error("Failed to save tenant domain: " . $e->getMessage(), [
                'tenant_id' => $this->tenantId,
                'domain'    => $this->domain,
            ]);

            // The old domain was never touched, so just remove the new one.
            $service->cleanupDomain($this->domain);
            $this->markFailed("Failed to save the new domain. Please contact support.");
            return;
        }

        Cache::forget("tenant_meta_{$oldDomain}");
        Cache::forget("tenant_meta_{$this->domain}");

        // 3. Only now that everything succeeded, remove the old domain.
        if ($oldDomain && $oldDomain !== $this->domain) {
            $service->cleanupDomain($oldDomain);
        }

        Log::info("Custom domain switched", [
            'tenant_id' => $this->tenantId,
            'old'       => $oldDomain,
            'new'       => $this->domain,
        ]);
    }

    /** Called by Laravel on uncaught exceptions. */
    public function failed(\Throwable $e): void
    {
        Log::error("SetupCustomDomainJob crashed: " . $e->getMessage(), [
            'tenant_id' => $this->tenantId,
            'domain'    => $this->domain,
        ]);

        $this->markFailed("Unexpected error while configuring the domain. Please try again or contact support.");
    }

    private function markFailed(string $message): void
    {
        Log::warning("Domain setup failed", [
            'tenant_id' => $this->tenantId,
            'domain'    => $this->domain,
            'reason'    => $message,
        ]);

        // A failed attempt does NOT touch domain_changed_at, so it never starts the cooldown.
        $this->tenants()
            ->where('id', $this->tenantId)
            ->where('pending_domain', $this->domain)
            ->update([
                'pending_domain' => null,
                'domain_status'  => 'failed',
                'domain_error'   => mb_substr($message, 0, 1000),
                'updated_at'     => now(),
            ]);
    }

    private function tenants()
    {
        return DB::connection('LMS_CENTER')->table('tenants');
    }
}
