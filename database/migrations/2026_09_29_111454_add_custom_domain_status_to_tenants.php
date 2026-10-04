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
        Schema::connection('LMS_CENTER')->table('tenants', function (Blueprint $table) {
            // $table->string('pending_domain')->nullable();
            // $table->string('domain_status', 20)->default('active'); // active | pending | failed
            // $table->text('domain_error')->nullable();
            // $table->timestamp('domain_requested_at')->nullable();

            // $table->unique('pending_domain', 'tenants_pending_domain_unique');
        });
    }

    public function down(): void
    {
        Schema::connection('LMS_CENTER')->table('tenants', function (Blueprint $table) {
            // $table->dropUnique('tenants_pending_domain_unique');
            // $table->dropColumn(['domain_status', 'domain_error', 'domain_requested_at']);
        });
    }
};
