<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_payments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('subscription_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('provider',30)->default('mercado_pago');
            $table->string('external_reference')->unique();
            $table->string('provider_payment_id')->nullable()->unique();
            $table->string('preference_id')->nullable();
            $table->string('status',30)->default('pending');
            $table->decimal('amount',12,2);
            $table->decimal('refunded_amount',12,2)->default(0);
            $table->string('currency',3)->default('BRL');
            $table->timestamp('paid_at')->nullable();
            $table->json('provider_data')->nullable();
            $table->timestamps();
            $table->index(['user_id','status']);
        });
        Schema::create('payment_webhook_events', function (Blueprint $table): void {
            $table->id();
            $table->string('provider',30);
            $table->string('event_key')->unique();
            $table->string('event_type')->nullable();
            $table->json('payload');
            $table->string('status',30)->default('received');
            $table->text('error')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });
        Schema::create('subscription_payment_refunds', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('subscription_payment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('provider_refund_id')->nullable()->unique();
            $table->decimal('amount',12,2);
            $table->string('status',30)->default('pending');
            $table->text('reason')->nullable();
            $table->json('provider_data')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('subscription_payment_refunds');
        Schema::dropIfExists('payment_webhook_events');
        Schema::dropIfExists('subscription_payments');
    }
};
