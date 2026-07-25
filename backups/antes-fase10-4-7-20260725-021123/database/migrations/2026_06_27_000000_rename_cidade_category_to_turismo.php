<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $turismoExists = DB::table('categories')->where('slug', 'turismo')->exists();

        if (! $turismoExists) {
            DB::table('categories')
                ->where('slug', 'cidade')
                ->update([
                    'name' => 'Turismo',
                    'slug' => 'turismo',
                    'updated_at' => now(),
                ]);

            return;
        }

        DB::table('categories')
            ->where('slug', 'cidade')
            ->update([
                'is_active' => false,
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        $cidadeExists = DB::table('categories')->where('slug', 'cidade')->exists();

        if (! $cidadeExists) {
            DB::table('categories')
                ->where('slug', 'turismo')
                ->update([
                    'name' => 'Cidade',
                    'slug' => 'cidade',
                    'updated_at' => now(),
                ]);
        }
    }
};
