<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */

    public function up(): void
    {
        Schema::connection('LMS_CENTER')->table('tenants', function (Blueprint $table) {});
    }

    public function down(): void
    {
        Schema::connection('LMS_CENTER')->table('tenants', function (Blueprint $table) {
            // $table->dropUnique('tenants_pending_domain_unique');
            // $table->dropColumn(['pending_domain', 'domain_status', 'domain_error', 'domain_requested_at']);
        });
    }
};
