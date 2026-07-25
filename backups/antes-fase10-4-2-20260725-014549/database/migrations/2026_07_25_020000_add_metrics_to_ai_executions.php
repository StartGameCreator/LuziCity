<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('ai_executions')) {
            return;
        }

        $columns = [
            'total_tokens' => fn (Blueprint $table) => $table->unsignedInteger('total_tokens')->default(0),
            'estimated_cost' => fn (Blueprint $table) => $table->decimal('estimated_cost', 18, 6)->default(0),
            'model' => fn (Blueprint $table) => $table->string('model', 160)->nullable(),
            'status_code' => fn (Blueprint $table) => $table->unsignedSmallInteger('status_code')->nullable(),
            'error_type' => fn (Blueprint $table) => $table->string('error_type', 120)->nullable(),
        ];

        foreach ($columns as $name => $definition) {
            if (! Schema::hasColumn('ai_executions', $name)) {
                Schema::table('ai_executions', $definition);
            }
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('ai_executions')) {
            return;
        }

        $columns = collect(['total_tokens', 'estimated_cost', 'model', 'status_code', 'error_type'])
            ->filter(fn (string $column) => Schema::hasColumn('ai_executions', $column))
            ->all();

        if ($columns !== []) {
            Schema::table('ai_executions', fn (Blueprint $table) => $table->dropColumn($columns));
        }
    }
};
