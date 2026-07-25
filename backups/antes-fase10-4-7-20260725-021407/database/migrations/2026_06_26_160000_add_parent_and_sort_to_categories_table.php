<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->foreignId('parent_id')->nullable()->after('id')->constrained('categories')->nullOnDelete();
            $table->unsignedInteger('sort_order')->default(0)->after('description');

            $table->index(['parent_id', 'sort_order']);
        });

        foreach ([
            'Turismo',
            'Política',
            'Economia',
            'Cultura',
            'Tecnologia',
            'Esportes',
            'Ciência e Tecnologia',
            'Música',
            'TV e Cinema',
            'Entretenimento',
        ] as $index => $name) {
            DB::table('categories')->updateOrInsert(
                ['slug' => str($name)->slug()->toString()],
                [
                    'parent_id' => null,
                    'name' => $name,
                    'description' => null,
                    'sort_order' => $index + 1,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropConstrainedForeignId('parent_id');
            $table->dropColumn('sort_order');
        });
    }
};
