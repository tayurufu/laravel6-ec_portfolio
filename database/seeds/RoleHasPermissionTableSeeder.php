<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleHasPermissionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (Config::get('database.default') === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        }
        DB::table('role_has_permissions')->truncate();

        // admin
        $permissions = [
            'admin_permission',
            'manager_permission',
            'staff_permission',
            'edit_item',
            'edit_master',
        ];
        $role = Role::findByName('admin');
        $role->givePermissionTo($permissions);

        // manager
        $permissions = [
            'manager_permission',
            'staff_permission',
            'edit_item',
            'edit_master',
        ];
        $role = Role::findByName('manager');
        $role->givePermissionTo($permissions);

        // staff
        $permissions = [
            'staff_permission',
            'edit_item'
        ];
        $role = Role::findByName('staff');
        $role->givePermissionTo($permissions);

        // customer
        $permissions = [
            'customer_permission',
        ];
        $role = Role::findByName('customer');
        $role->givePermissionTo($permissions);

        if (Config::get('database.default') === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }
}
