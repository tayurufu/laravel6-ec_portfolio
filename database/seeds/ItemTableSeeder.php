<?php

use App\Models\Item;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

use Faker\Factory as Faker;

class ItemTableSeeder extends Seeder
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
        Item::truncate();

        $faker = Faker::create('ja_JP');

        //
//        for($i = 1; $i <= 5; $i++) {
//            Item::create([
//                'item_id' => sprintf('%20d', $i),
//                'price' => 1000 * $i,
//                'name' => 'item_test_display_' . $i,
//                'type_id' => \App\Models\ItemType::inRandomOrder()->first()->id,
//                'description' => $faker->text(500)
//            ]);
//        }


        $items = [
            ['夏目漱石 坊っちゃん', 500, '本'],
            ['ゲーテ ファウスト', 600, '本'],
            ['芥川龍之介 プロレタリア文学論', 1000, '本'],
            ['チェス', 4200, 'ゲーム'],
            ['野球', 7000, 'ゲーム'],
            ['ゴルフ', 9000, 'ゲーム'],
            ['エアコン', 30000, '家電'],
            ['掃除機', 15000, '家電'],
            ['食パン', 100, '食料品'],
            ['米 2kg', 1000, '食料品'],
            ['ビール 350ml', 200, '酒'],
            ['ワインA 700ml', 1000, '酒'],
            ['ワインB 700ml', 9800, '酒'],
        ];

        for($i = 0; $i < count($items); $i++) {

            $type_id = $this->getItemTypeId($items[$i][2]);

            if($type_id === null){
                continue;
            }

            Item::create([
                'item_id' => sprintf('%020d', ($i + 1)),
                'price' => $items[$i][1],
                'name' => $items[$i][0],
                'type_id' => $type_id,
                'description' => $faker->text(500)
            ]);
        }


        if (Config::get('database.default') === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }

    private function getItemTypeId($value)
    {
        return DB::table('item_types')->where('name', $value)->value('id');
    }
}
