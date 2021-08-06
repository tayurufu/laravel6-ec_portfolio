<?php

use Illuminate\Database\Seeder;
use App\User;

class AclUserTableSeeder extends Seeder
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

        User::truncate();

        // admin
        $user = User::create([
            'name'     => 'web管理責任者',
            'email'    => 'admin@gmail.com',
            'password' => Hash::make('admin'),
        ]);
        $user->assignRole('admin');
        // manager
        $user = User::create([
            'name'     => 'サイトマネージャ',
            'email'    => 'manager@gmail.com',
            'password' => Hash::make('manager'),
        ]);
        $user->assignRole('manager');
        // staff
        $user = User::create([
            'name'     => 'スタッフ',
            'email'    => 'staff@gmail.com',
            'password' => Hash::make('staff'),
        ]);
        $user->assignRole('staff');
        // customer
        $user = User::create([
            'name'     => 'カスタマー',
            'email'    => 'customer@gmail.com',
            'password' => Hash::make('customer'),
        ]);
        $user->assignRole('customer');

        if (Config::get('database.default') === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }

    }
}
