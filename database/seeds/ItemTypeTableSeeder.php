<?php

use App\Models\ItemType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ItemTypeTableSeeder extends Seeder
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
        ItemType::truncate();

        $itemtype = ['本', 'ゲーム', '家電', '食料品', '酒', 'パソコン', '薬', '服', '靴',];

        for ($i = 0; $i < count($itemtype); $i++){
            ItemType::create(['type_id' =>  sprintf('%04d', ($i + 1)), 'name' => $itemtype[$i]]);
        }


        if (Config::get('database.default') === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }
}
