<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ItemTagTableSeeder extends Seeder
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
        DB::table('item_tag')->truncate();

        //
//        for($i = 1; $i <= 3; $i++){
//            for($j = 1; $j <= 10; $j++) {
//                DB::table('item_tag')->insert([
//                    'item_name' => 'item_test' . $i,
//                    'tag_id' => $j,
//                ]);
//            }
//        }

        $data = [
            '夏目漱石 坊っちゃん' => ['単行本'],
            'ゲーテ ファウスト' => ['洋書'],
            '芥川龍之介 プロレタリア文学論' => ['単行本', '新書'],
            'エアコン' => ['生活家電'],
            'ビール 350ml' => ['ビール'],
            'ワインA 700ml' => ['ワイン'],
            'ワインB 700ml' => ['ワイン'],
        ];

        foreach($data as $k => $v){

            $item_id = $this->getItemId($k);

            if($item_id === null){
                continue;
            }

            foreach($v as $v2){

                $tag_id = $this->getTagId($v2);

                if($tag_id == null){
                    continue;
                }

                DB::table('item_tag')->insert([
                    'item_id' => $item_id,
                    'tag_id' => $tag_id,
                ]);
            }
        }


        if (Config::get('database.default') === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }

    private function getTagId($value)
    {
        return DB::table('tags')->where('name', $value)->value('id');
    }

    private function getItemId($value)
    {
        return DB::table('items')->where('name', $value)->value('id');
    }
}
