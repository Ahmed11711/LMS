<?php

namespace App\Observers;

use App\Models\Tenant;
use Illuminate\Support\Facades\Cache;

class TenantObserver
{
    /**
     * Handle the Tenant "created" event.
     */
    public function created(Tenant $tenant): void
    {
        Cache::forget("tenant_meta_{$tenant->domain}");
    }

    /**
     * Handle the Tenant "updated" event.
     */
    public function updated(Tenant $tenant): void {}

    /**
     * Handle the Tenant "deleted" event.
     */
    public function deleted(Tenant $tenant): void
    {
        //
    }

    /**
     * Handle the Tenant "restored" event.
     */
    public function restored(Tenant $tenant): void
    {
        //
    }

    /**
     * Handle the Tenant "force deleted" event.
     */
    public function forceDeleted(Tenant $tenant): void
    {
        //
    }
}
