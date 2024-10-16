<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Laravue\Models\Role;
use App\Laravue\Models\Permission;
use App\Laravue\Acl;

class SetupRolePermissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach (Acl::roles() as $role) {
            Role::findOrCreate($role);
        }

        $superAdminRole = Role::findByName(Acl::ROLE_SUPER_ADMIN);
        $directivoRole = Role::findByName(Acl::ROLE_DIRECTIVO);
        $adminRole = Role::findByName(Acl::ROLE_ADMIN);
        $socioRole = Role::findByName(Acl::ROLE_SOCIO);
        $inquilinoRole = Role::findByName(Acl::ROLE_INQUILINO);

        foreach (Acl::permissions() as $permission) {
            Permission::findOrCreate($permission, 'api');
        }

        // Setup basic permission
        $superAdminRole->givePermissionTo(Acl::permissions());
        $directivoRole->givePermissionTo(Acl::permissions([Acl::PERMISSION_PERMISSION_MANAGE]));
        $adminRole->givePermissionTo(Acl::menuPermissions());
        $socioRole->givePermissionTo(Acl::PERMISSION_ARTICLE_MANAGE);
        $inquilinoRole->givePermissionTo([
            Acl::PERMISSION_VIEW_MENU_ELEMENT_UI,
            Acl::PERMISSION_VIEW_MENU_PERMISSION,
        ]);

        foreach (Acl::roles() as $role) {
            /** @var \App\User[] $users */
            $users = \App\Laravue\Models\User::where('role', $role)->get();
            $role = Role::findByName($role);
            foreach ($users as $user) {
                $user->syncRoles($role);
            }
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('role')->default('editor');
            });
        }

        /** @var \App\User[] $users */
        $users = \App\Laravue\Models\User::all();
        foreach ($users as $user) {
            $roles = array_reverse(Acl::roles());
            foreach ($roles as $role) {
                if ($user->hasRole($role)) {
                    $user->role = $role;
                    $user->save();
                }
            }
        }
    }
}
