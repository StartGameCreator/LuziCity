<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('request_metrics', function (Blueprint $table): void {
            $table->id();
            $table->uuid('request_id')->index();
            $table->string('method', 10);
            $table->string('route', 255)->nullable();
            $table->string('path', 255);
            $table->unsignedSmallInteger('status')->index();
            $table->unsignedInteger('duration_ms');
            $table->unsignedBigInteger('memory_bytes')->default(0);
            $table->boolean('is_api')->default(false);
            $table->timestamp('occurred_at')->index();
            $table->index(['status', 'occurred_at']);
            $table->index(['route', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('request_metrics');
    }
};
