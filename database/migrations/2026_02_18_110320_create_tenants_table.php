<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->string('name');
            $table->string('domain')->unique();

            $table->string('db_name');
            $table->string('db_user');
            $table->string('db_pass');
            $table->string('db_host')->default('127.0.0.1');

            $table->boolean('active')->default(true);
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('suspended_at')->nullable();
            $table->string('plan_id')->nullable();
            $table->json('settings')->nullable();


            $table->timestamps();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
