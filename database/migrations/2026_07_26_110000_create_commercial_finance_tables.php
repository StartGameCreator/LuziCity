<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commercial_invoices', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('advertiser_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('commercial_proposal_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('number', 40)->unique();
            $table->string('description');
            $table->string('status', 30)->default('pending');
            $table->decimal('amount', 14, 2);
            $table->decimal('paid_amount', 14, 2)->default(0);
            $table->date('issued_at');
            $table->date('due_at');
            $table->timestamp('paid_at')->nullable();
            $table->boolean('is_recurring')->default(false);
            $table->string('recurrence', 20)->nullable();
            $table->date('next_renewal_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['status', 'due_at']);
            $table->index(['advertiser_profile_id', 'status']);
        });

        Schema::create('commercial_payments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('commercial_invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('amount', 14, 2);
            $table->timestamp('paid_at');
            $table->string('method', 30);
            $table->string('reference', 120)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commercial_payments');
        Schema::dropIfExists('commercial_invoices');
    }
};
