<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('webhook_endpoints', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 120);
            $table->string('url', 2048);
            $table->text('secret');
            $table->json('events');
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });
        Schema::create('webhook_deliveries', function (Blueprint $table): void {
            $table->id();
            $table->uuid('event_id')->unique();
            $table->foreignId('webhook_endpoint_id')->constrained()->cascadeOnDelete();
            $table->string('event', 80)->index();
            $table->json('payload');
            $table->string('status', 30)->default('pending')->index();
            $table->unsignedSmallInteger('attempts')->default(0);
            $table->unsignedSmallInteger('response_status')->nullable();
            $table->text('response_body')->nullable();
            $table->text('error')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_deliveries');
        Schema::dropIfExists('webhook_endpoints');
    }
};
