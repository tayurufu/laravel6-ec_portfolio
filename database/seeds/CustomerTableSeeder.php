<?php

use Illuminate\Database\Seeder;

class CustomerTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        if (Config::get('database.default') === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        }
        \App\Models\Customer::truncate();


        $users = \App\User::all();
        foreach($users as $user) {
            \App\Models\Customer::create([
                'customer_name' => 'test_customer_' . $user->id,
                'user_id' => $user->id,
                'tel_no' => "1234567890",
                'post_no' => '111-1111',
                'address1' => 'some where1',
                'address2' => 'some where2',
                'address3' => 'some where3',
            ]);
        }

        if (Config::get('database.default') === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }

    }
}
