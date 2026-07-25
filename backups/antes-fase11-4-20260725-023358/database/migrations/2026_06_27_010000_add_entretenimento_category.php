<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $tvOrder = DB::table('categories')
            ->where('slug', 'tv-e-cinema')
            ->value('sort_order');

        DB::table('categories')->updateOrInsert(
            ['slug' => 'entretenimento'],
            [
                'parent_id' => null,
                'name' => 'Entretenimento',
                'description' => null,
                'sort_order' => ((int) $tvOrder ?: 9) + 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        DB::table('categories')->where('slug', 'entretenimento')->delete();
    }
};
