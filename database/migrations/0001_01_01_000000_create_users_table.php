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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();

            $table->string('email')->unique()->nullable();
            $table->string('phone')->index()->nullable();

            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');

            $table->enum('role', ['super_admin', 'academy', 'admin', 'student'])->index();
            $table->enum('status_payment', ['paid', 'free_trial', 'pending', 'expired', ''])->index()->default('free_trial');
            $table->boolean('is_active')->default(true)->index();

            $table->string('phone_academy')->nullable();
            $table->string('username')->unique()->nullable();

            $table->string('country_code', 10)->nullable();

            $table->string('specialties')->nullable();
            $table->string('profile_image')->nullable();

            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
