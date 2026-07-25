<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $roles = [
            'Super Admin',
            'Admin',
            'Editor Chefe',
            'Editor',
            'Autor',
            'Jornalista',
            'Colunista',
            'Anunciante',
            'Patrocinador',
            'Assinante',
            'Moderador',
            'Usuario',
        ];

        foreach ($roles as $role) {
            Role::findOrCreate($role);
        }

        if (env('ADMIN_EMAIL') && env('ADMIN_PASSWORD')) {
            $admin = User::firstOrCreate([
                'email' => env('ADMIN_EMAIL'),
            ], [
                'name' => env('ADMIN_NAME', 'Administrador Luzicity'),
                'password' => Hash::make(env('ADMIN_PASSWORD')),
            ]);

            $admin->syncRoles(['Super Admin', 'Admin']);
        }
    }
}
