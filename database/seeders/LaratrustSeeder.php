<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LaratrustSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('permission_role')->truncate();
        DB::table('permission_user')->truncate();
        DB::table('permissions')->truncate();
        DB::table('roles')->truncate();
        DB::table('role_user')->truncate();
        DB::table('users')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Roles & permissions
        $roles = [
            'administrator' => ['sudo'],
            'operator' => [],
        ];

        $displayNames = [
            'administrator' => 'Administrator',
            'operator' => 'Operator',
        ];

        foreach ($roles as $name => $permissions) {
            $role = \App\Models\Role::firstOrCreate(
                ['name' => $name],
                ['display_name' => $displayNames[$name]]
            );

            foreach ($permissions as $perm) {
                $permission = \App\Models\Permission::firstOrCreate(
                    ['name' => $perm],
                    ['display_name' => ucfirst(str_replace('_', ' ', $perm))]
                );

                // Attach permission
                $role->permissions()->syncWithoutDetaching([$permission->id]);
            }
        }

        //Master User
        $user = \App\Models\User::create([
            'sso_id' => 1,
            'name' => 'Harry Rahman Rangkuti',
            'email' => 'harryrahman2768@gmail.com',
            'unit_id' => 1,
            'password' => bcrypt('1234567890'),
            'photo' => null,
            'active_role_id' => 1,
        ]);

        $user->addRole('administrator');
    }
}