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
        $admin = User::create([
            'nombre' => 'Admin',
            'apellidoPaterno' => 'Admin',
            'apellidoMaterno' => 'Admin',
            'email' => 'admin@laravue.dev',
            'telefono' => '1234567890',
            'idStatus' => 1,
            'password' => Hash::make('laravue'),
        ]);
        $manager = User::create([
            'nombre' => 'Manager',
            'apellidoPaterno' => 'Manager',
            'apellidoMaterno' => 'Manager',
            'email' => 'manager@laravue.dev',
            'telefono' => '1234567890',
            'idStatus' => 1,
            'password' => Hash::make('laravue'),
        ]);
        $editor = User::create([
            'nombre' => 'Editor',
            'apellidoPaterno' => 'Editor',
            'apellidoMaterno' => 'Editor',
            'email' => 'editor@laravue.dev',
            'telefono' => '1234567890',
            'idStatus' => 1,
            'password' => Hash::make('laravue'),
        ]);
        $user = User::create([
            'nombre' => 'User',
            'apellidoPaterno' => 'User',
            'apellidoMaterno' => 'User',
            'email' => 'user@laravue.dev',
            'telefono' => '1234567890',
            'idStatus' => 1,
            'password' => Hash::make('laravue'),
        ]);
        $visitor = User::create([
            'nombre' => 'Visitor',
            'apellidoPaterno' => 'Visitor',
            'apellidoMaterno' => 'Visitor',
            'email' => 'visitor@laravue.dev',
            'telefono' => '1234567890',
            'idStatus' => 1,
            'password' => Hash::make('laravue'),
        ]);

        $adminRole = Role::findByName(\App\Laravue\Acl::ROLE_ADMIN);
        $managerRole = Role::findByName(\App\Laravue\Acl::ROLE_MANAGER);
        $editorRole = Role::findByName(\App\Laravue\Acl::ROLE_EDITOR);
        $userRole = Role::findByName(\App\Laravue\Acl::ROLE_USER);
        $visitorRole = Role::findByName(\App\Laravue\Acl::ROLE_VISITOR);
        $admin->syncRoles($adminRole);
        $manager->syncRoles($managerRole);
        $editor->syncRoles($editorRole);
        $user->syncRoles($userRole);
        $visitor->syncRoles($visitorRole);
        $this->call(UsersTableSeeder::class);
    }
}
