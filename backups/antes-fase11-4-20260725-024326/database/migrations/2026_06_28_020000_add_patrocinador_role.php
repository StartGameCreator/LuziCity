<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        Role::findOrCreate('Patrocinador');
    }

    public function down(): void
    {
        Role::query()->where('name', 'Patrocinador')->delete();
    }
};
