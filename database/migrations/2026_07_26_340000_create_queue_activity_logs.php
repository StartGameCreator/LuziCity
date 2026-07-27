<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('queue_activity_logs', function (Blueprint $table): void {
            $table->id();
            $table->uuid('job_uuid')->nullable()->index();
            $table->string('connection', 80);
            $table->string('queue', 120);
            $table->string('job_name', 255);
            $table->string('status', 20)->index();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->text('error')->nullable();
            $table->timestamp('occurred_at')->index();
            $table->index(['queue', 'status', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('queue_activity_logs');
    }
};
