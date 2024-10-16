<?php

use App\Laravue\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Laravue\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $superAdmin = User::create([
            'nombre' => 'Admin',
            'apellidoPaterno' => 'Admin',
            'apellidoMaterno' => 'Admin',
            'email' => 'admin@laravue.dev',
            'telefono' => '1234567890',
            'idStatus' => 1,
            'password' => Hash::make('laravue'),
        ]);
        $directivo = User::create([
            'nombre' => 'Directivo',
            'apellidoPaterno' => 'Directivo',
            'apellidoMaterno' => 'Directivo',
            'email' => 'directivo@laravue.dev',
            'telefono' => '1234567890',
            'idStatus' => 1,
            'password' => Hash::make('laravue'),
        ]);
        $admin = User::create([
            'nombre' => 'Administrador',
            'apellidoPaterno' => 'Administrador',
            'apellidoMaterno' => 'Administrador',
            'email' => 'administrador@laravue.dev',
            'telefono' => '1234567890',
            'idStatus' => 1,
            'password' => Hash::make('laravue'),
        ]);
        $socio = User::create([
            'nombre' => 'Socio',
            'apellidoPaterno' => 'Socio',
            'apellidoMaterno' => 'Socio',
            'email' => 'Socio@laravue.dev',
            'telefono' => '1234567890',
            'idStatus' => 1,
            'password' => Hash::make('laravue'),
        ]);
        $inquilino = User::create([
            'nombre' => 'Inquilino',
            'apellidoPaterno' => 'Inquilino',
            'apellidoMaterno' => 'Inquilino',
            'email' => 'Inquilino@laravue.dev',
            'telefono' => '1234567890',
            'idStatus' => 1,
            'password' => Hash::make('laravue'),
        ]);

        $superAdminRole = Role::findByName(\App\Laravue\Acl::ROLE_SUPER_ADMIN);
        $directivoRole = Role::findByName(\App\Laravue\Acl::ROLE_DIRECTIVO);
        $adminRole = Role::findByName(\App\Laravue\Acl::ROLE_ADMIN);
        $socioRole = Role::findByName(\App\Laravue\Acl::ROLE_SOCIO);
        $inquilinoRole = Role::findByName(\App\Laravue\Acl::ROLE_INQUILINO);
        $superAdmin->syncRoles($superAdminRole);
        $directivo->syncRoles($directivoRole);
        $admin->syncRoles($adminRole);
        $socio->syncRoles($socioRole);
        $inquilino->syncRoles($inquilinoRole);
        $this->call(UsersTableSeeder::class);
    }
}
