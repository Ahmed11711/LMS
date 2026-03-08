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
        Schema::create('tenant_feature_usage', function (Blueprint $table) {
            $table->id();
            $table->string('feature_slug')->unique(); //  : 'max_courses'
            $table->decimal('total_limit')->default(0);
            $table->decimal('used_amount')->default(0);
            $table->boolean('is_enabled')->default(true);
            $table->enum('type', ['numeric', 'boolean'])->default('numeric');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_feature_usages');
    }
};
