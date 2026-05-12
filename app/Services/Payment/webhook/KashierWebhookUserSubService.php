<?php

namespace App\Services\Payment\webhook;

use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class KashierWebhookUserSubService
{
    public function handle(array $payload, string $encodedTenantId): void
    {
        $domain = $this->decodeTenantId($encodedTenantId);
        $data   = $payload['data'] ?? null;

        if (!$data) {
            Log::warning('Kashier webhook: missing data payload', $payload);
            return;
        }

        $transactionId = $data['merchantOrderId'] ?? null;
        $status        = $data['status'] ?? null;

        if (!$transactionId || !$status) {
            Log::warning('Kashier webhook: missing transactionId or status', $data);
            return;
        }

        $tenant = $this->resolveTenant($domain);

        if (!$tenant) {
            Log::error('Kashier webhook: tenant not found', ['domain' => $domain]);
            return;
        }

        $this->connectToTenantDatabase($tenant);

        $newStatus = $this->resolveStatus($status);

        $updated = $this->updateSubscription($transactionId, $newStatus);

        if ($updated && $newStatus === 'active') {
            $this->handleWallets($transactionId);
        }

        Log::info('Kashier webhook: done', [
            'domain'        => $domain,
            'transactionId' => $transactionId,
            'status'        => $newStatus,
        ]);
    }

    private function updateSubscription(string $transactionId, string $status): bool
    {
        $updated = DB::connection('tenant')
            ->table('user_subscribes')
            ->where('transaction_id', $transactionId)
            ->update(['status' => $status]);

        if (!$updated) {
            Log::warning('Kashier webhook: no subscription found', [
                'transaction_id' => $transactionId,
            ]);
            return false;
        }

        return true;
    }

    private function handleWallets(string $transactionId): void
    {
        $subscribe = DB::connection('tenant')
            ->table('user_subscribes')
            ->where('transaction_id', $transactionId)
            ->first();

        if (!$subscribe) {
            Log::warning('Kashier webhook: subscription not found for wallet', [
                'transaction_id' => $transactionId,
            ]);
            return;
        }

        $course = DB::connection('tenant')
            ->table('courses')
            ->where('id', $subscribe->course_id)
            ->first();

        if (!$course) {
            Log::warning('Kashier webhook: course not found', [
                'course_id' => $subscribe->course_id,
            ]);
            return;
        }

        //  add amount for instractour
        $this->creditUser($course->user_id, (float) $course->price, $transactionId, 'teacher');

        // جيب الـ super_admin
        $superAdmin = DB::connection('tenant')
            ->table('users')
            ->where('role', 'admin')
            ->first();

        if (!$superAdmin) {
            Log::warning('Kashier webhook: super_admin not found');
            return;
        }

        // ضيف للأكاديمية
        $this->creditUser($superAdmin->id, (float) $course->price, $transactionId, 'academy');
    }

    private function creditUser(int $userId, float $amount, string $transactionId, string $type): void
    {
        $balance = DB::connection('tenant')
            ->table('user_balances')
            ->where('user_id', $userId)
            ->first();

        $balanceBefore = $balance ? (float) $balance->balance : 0;
        $balanceAfter  = $balanceBefore + $amount;

        if ($balance) {
            DB::connection('tenant')
                ->table('user_balances')
                ->where('user_id', $userId)
                ->update([
                    'balance'    => $balanceAfter,
                    'updated_at' => now(),
                ]);
        } else {
            DB::connection('tenant')
                ->table('user_balances')
                ->insert([
                    'user_id'    => $userId,
                    'balance'    => $balanceAfter,
                    'available_balance' => 0,
                    'is_active'  => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
        }

        DB::connection('tenant')
            ->table('user_transactions')
            ->insert([
                'user_id'        => $userId,
                'type'           => 'deposit',
                'amount'         => $amount,
                'status'         => 'pending',
                'notes'          => 'Course purchase - ' . $type,
                'balance_before' => $balanceBefore,
                'balance_after'  => $balanceAfter,
                'transaction_id' => $transactionId,
                'available_at'   => now()->addDays(7),
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);

        Log::info('Kashier webhook: wallet credited', [
            'user_id'        => $userId,
            'type'           => $type,
            'amount'         => $amount,
            'balance_before' => $balanceBefore,
            'balance_after'  => $balanceAfter,
        ]);
    }

    // --------------------------------------------------------

    private function decodeTenantId(string $encoded): string
    {
        return base64_decode(
            str_pad(
                strtr($encoded, '-_', '+/'),
                strlen($encoded) % 4,
                '=',
                STR_PAD_RIGHT
            )
        );
    }

    private function resolveTenant(string $domain): ?Tenant
    {
        return Tenant::where('domain', $domain)->first();
    }

    private function connectToTenantDatabase(Tenant $tenant): void
    {
        config([
            'database.connections.tenant' => [
                'driver'    => 'mysql',
                'host'      => $tenant->db_host,
                'database'  => $tenant->db_name,
                'username'  => $tenant->db_user,
                'password'  => $tenant->db_pass,
                'charset'   => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
            ],
        ]);

        DB::purge('tenant');
        DB::reconnect('tenant');
    }

    private function resolveStatus(string $kashierStatus): string
    {
        return match ($kashierStatus) {
            'SUCCESS'             => 'active',
            'FAILED', 'CANCELLED' => 'cancelled',
            default               => 'pending',
        };
    }
}
