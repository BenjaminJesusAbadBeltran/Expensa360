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
            "Adriana C. Ocampo Uria",
            "Albert Einstein",
            "Anna K. Behrensmeyer",
            "Blaise Pascal",
            "Caroline Herschel",
        ];

        foreach (array_slice($userList, 0, 5) as $fullName) {
            $name = str_replace(' ', '.', $fullName);
            $roleName = \Illuminate\Support\Arr::random([
                Acl::ROLE_MANAGER,
                Acl::ROLE_EDITOR,
                Acl::ROLE_USER,
                Acl::ROLE_VISITOR,
            ]);
            $user = User::create([
                'nombre' => explode(' ', $fullName)[0],
                'apellidoPaterno' => explode(' ', $fullName)[1],
                'apellidoMaterno' => explode(' ', $fullName)[2] ?? '',
                'email' => strtolower($name) . '@laravue.dev',
                'password' => Hash::make('laravue'),
                'telefono' => '1234567890',
                'idStatus' => 1,
            ]);

            $role = Role::findByName($roleName);
            $user->syncRoles($role);
        }
    }
}