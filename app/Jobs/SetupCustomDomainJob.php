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

    // Never retry automatically: each attempt can burn Let's Encrypt rate limits.
    public int $tries = 1;

    // Must be higher than the worst-case setup time (~260s) and LOWER than
    // `retry_after` in config/queue.php for the connection.
    public int $timeout = 420;

    public function __construct(
        public int|string $tenantId,
        public string $domain,
    ) {}

    public function handle(DomainService $service): void
    {
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

        // 2. Switch the tenant to the new domain.
        try {
            $this->tenants()->where('id', $this->tenantId)->update([
                'domain'         => $this->domain,
                'pending_domain' => null,
                'domain_status'  => 'active',
                'domain_error'   => null,
                'updated_at'     => now(),
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

    /** Called by Laravel on uncaught exceptions AND on worker timeout. */
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
