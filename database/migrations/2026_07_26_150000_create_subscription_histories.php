<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table): void {
            $table->timestamp('cancelled_at')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('cancellation_reason')->nullable();
        });

        Schema::create('subscription_histories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('subscription_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('performed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('event', 40);
            $table->string('from_status', 30)->nullable();
            $table->string('to_status', 30)->nullable();
            $table->foreignId('from_plan_id')->nullable()->constrained('subscription_plans')->nullOnDelete();
            $table->foreignId('to_plan_id')->nullable()->constrained('subscription_plans')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['user_id','created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_histories');
        Schema::table('subscriptions', function (Blueprint $table): void {
            $table->dropForeign(['cancelled_by']);
            $table->dropColumn(['cancelled_at','cancelled_by','cancellation_reason']);
        });
    }
};
