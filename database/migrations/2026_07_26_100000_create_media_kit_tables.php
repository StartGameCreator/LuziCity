<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_kit_formats', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('placement', 80);
            $table->string('dimensions', 80)->nullable();
            $table->text('description')->nullable();
            $table->decimal('price', 14, 2)->default(0);
            $table->string('billing_model', 20)->default('fixed');
            $table->unsignedInteger('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('commercial_proposals', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('advertiser_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('number', 40)->unique();
            $table->string('title');
            $table->string('status', 30)->default('draft');
            $table->date('valid_until')->nullable();
            $table->decimal('discount', 14, 2)->default(0);
            $table->decimal('total', 14, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->index(['advertiser_profile_id', 'status']);
        });

        Schema::create('commercial_proposal_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('commercial_proposal_id')->constrained()->cascadeOnDelete();
            $table->foreignId('media_kit_format_id')->nullable()->constrained()->nullOnDelete();
            $table->string('description');
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('unit_price', 14, 2);
            $table->decimal('subtotal', 14, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commercial_proposal_items');
        Schema::dropIfExists('commercial_proposals');
        Schema::dropIfExists('media_kit_formats');
    }
};
