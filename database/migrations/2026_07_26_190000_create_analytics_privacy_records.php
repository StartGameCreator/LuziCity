<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analytics_consents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('session_hash',64);
            $table->string('choice',20);
            $table->string('policy_version',20);
            $table->timestamp('consented_at');
            $table->timestamps();
            $table->index(['user_id','consented_at']);
        });
        Schema::create('privacy_data_requests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('session_hash',64);
            $table->string('type',30);
            $table->string('status',30)->default('completed');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('privacy_data_requests');
        Schema::dropIfExists('analytics_consents');
    }
};
