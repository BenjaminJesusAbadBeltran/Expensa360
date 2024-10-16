<?php

use Illuminate\Database\Seeder;
use App\Laravue\Acl;
use App\Laravue\Models\Role;
use App\Laravue\Models\User;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $userList = [
            "Benjamin J. Beltran",
            "Marisol Arguedas",
            "Rodolfo Rodriguez",
            "Blaise X. Pascal",
            "Caroline XX. Herschel",
        ];

        foreach (array_slice($userList, 0, 5) as $fullName) {
            $name = str_replace(' ', '.', $fullName);
            $roleName = \Illuminate\Support\Arr::random([
                Acl::ROLE_SUPER_ADMIN,
                Acl::ROLE_DIRECTIVO,
                Acl::ROLE_ADMIN,
                Acl::ROLE_SOCIO,
                Acl::ROLE_INQUILINO
            ]);
            $user = User::create([
                'nombre' => explode(' ', $fullName)[0],
                'apellidoPaterno' => explode(' ', $fullName)[1],
                'apellidoMaterno' => explode(' ', $fullName)[2] ?? '',
                'email' => strtolower($name) . '@laravue.dev',
                'password' => Hash::make('laravue'),
                'telefono' => '1234567890',
                'status' => 'Activo',
            ]);

            $role = Role::findByName($roleName);
            $user->syncRoles($role);
        }
    }
}