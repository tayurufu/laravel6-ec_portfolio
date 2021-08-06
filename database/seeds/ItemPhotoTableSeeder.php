<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ItemPhotoTableSeeder extends Seeder
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
        DB::table('item_photos')->truncate();

        /*
        $files = Storage::disk('photos')->files();
        foreach($files as $file){
            Storage::disk('photos')->delete($file);
        }
        */

//        for($i = 1; $i <= 3; $i++) {
//
//            for($j = 1; $j <= 3; $j++) {
//                $itemName = 'item_test' . $i;
//                $fileName =  $itemName . '_' . uniqid(rand()) . '.jpg';
//                DB::table('item_photos')->insert([
//                    'item_name' => $itemName,
//                    'order' => $j,
//                    'filename' => $fileName,
//                    'created_at' => DB::raw('NOW()'),
//                    'updated_at' => DB::raw('NOW()'),
//                ]);
//
//                //Storage::disk('photos')->copy('test.jpg', $fileName);
//            }
//        }

        $data = [
            '夏目漱石 坊っちゃん' => ['book1.jpeg'],
            'ゲーテ ファウスト' => ['book2.jpg'],
            '芥川龍之介 プロレタリア文学論' => ['book3.jpg'],
            'チェス' => ['chess1.jpeg'],
            '野球' => ['baseball1.jpeg'],
            'ゴルフ' => ['golf1.jpeg', 'golf2.jpeg', 'golf3.jpeg'],
            'エアコン' => ['airconditioner1.jpeg'],
            '食パン' => ['bread1.jpeg'],
            '米 2kg' => ['rice1.jpeg'],
            'ビール 350ml' => ['beer1.jpeg'],
            'ワインA 700ml' => ['wine1.jpeg'],
            'ワインB 700ml' => ['wine2.jpeg', 'wine3.jpeg'],
        ];

        foreach($data as $k => $v){

            $item_id = $this->getItemId($k);

            if($item_id === null){
                continue;
            }

            foreach($v as $k2 => $v2){

                DB::table('item_photos')->insert([
                    'item_id' => $item_id,
                    'order' => (int)$k2 + 1,
                    'filename' => $v2,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }
        }

        if (Config::get('database.default') === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }

    private function getItemId($value)
    {
        return DB::table('items')->where('name', $value)->value('id');
    }

}
