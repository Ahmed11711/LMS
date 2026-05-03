<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Repositories\UserSubscribe\UserSubscribeRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class KashierWebhookController extends Controller
{
    public function __construct(
        private UserSubscribeRepository $userSubscribeRepo,
    ) {}

    public function handle(Request $request, string $encodedDomain)
    {
        // 1. Decode tenant domain
        $domain = base64_decode($encodedDomain);

        // 2. Find tenant
        $tenant = DB::connection('LMS_CENTER')
            ->table('tenants')
            ->where('domain', $domain)
            ->where('active', 1)
            ->first();

        if (!$tenant) {
            Log::error('Kashier webhook: tenant not found', ['domain' => $domain]);
            return response()->json(['message' => 'Tenant not found'], 404);
        }

        // 3. Switch to tenant DB
        Config::set('database.connections.tenant.host', $tenant->db_host);
        Config::set('database.connections.tenant.database', $tenant->db_name);
        Config::set('database.connections.tenant.username', $tenant->db_user);
        Config::set('database.connections.tenant.password', $tenant->db_pass);
        DB::purge('tenant');
        DB::reconnect('tenant');
        Config::set('database.default', 'tenant');

        // 4. Get transaction reference
        $transactionId = $request->input('order');
        $status        = $request->input('status'); // 'SUCCESS' or 'FAILED'

        Log::info('Kashier webhook received', [
            'domain'        => $domain,
            'transactionId' => $transactionId,
            'status'        => $status,
        ]);

        // 5. Update subscription status
        $subscription = $this->userSubscribeRepo->findByTransactionId($transactionId);

        if (!$subscription) {
            Log::error('Kashier webhook: subscription not found', ['transactionId' => $transactionId]);
            return response()->json(['message' => 'Subscription not found'], 404);
        }

        $subscription->update([
            'status' => $status === 'SUCCESS' ? 'active' : 'failed',
        ]);

        Log::info('Kashier webhook: subscription updated', [
            'transactionId' => $transactionId,
            'status'        => $status,
        ]);

        return response()->json(['message' => 'Webhook handled successfully'], 200);
    }
}
