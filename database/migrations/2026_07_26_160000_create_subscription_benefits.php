<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_benefits', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('type', 30);
            $table->text('description')->nullable();
            $table->string('code', 80)->nullable()->unique();
            $table->text('destination_url')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->unsignedInteger('usage_limit')->nullable();
            $table->unsignedInteger('redeemed_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['type','is_active','starts_at','ends_at'],'subscription_benefits_availability_index');
        });

        Schema::create('subscription_benefit_plan', function (Blueprint $table): void {
            $table->foreignId('subscription_benefit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subscription_plan_id')->constrained()->cascadeOnDelete();
            $table->primary(['subscription_benefit_id','subscription_plan_id'],'benefit_plan_primary');
        });

        Schema::create('subscription_benefit_redemptions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('subscription_benefit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('redeemed_at');
            $table->string('status', 30)->default('redeemed');
            $table->timestamps();
            $table->unique(['subscription_benefit_id','user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_benefit_redemptions');
        Schema::dropIfExists('subscription_benefit_plan');
        Schema::dropIfExists('subscription_benefits');
    }
};
