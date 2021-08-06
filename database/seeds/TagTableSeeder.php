<?php

use Illuminate\Database\Seeder;

use App\Models\Tag;

class TagTableSeeder extends Seeder
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
        Tag::truncate();

        $tags = ['洋書', 'コミック', '雑誌', '単行本', '文庫', '新書', 'CD', 'DVD', 'ブルーレイ',
            'キッチン家電', '生活家電', '照明', '大型家電', 'カメラ', 'PC', 'テレビ', '肉', '魚',
            'ビール', 'ワイン', '日本酒', '頭痛薬', '胃薬', 'スニーカー', '革靴', 'メンズ服', 'レディース服'
        ];

        for ($i = 0; $i < count($tags); $i++){
            Tag::create(['tag_id' =>  sprintf('%04d', ($i + 1)), 'name' => $tags[$i]]);
        }



        if (Config::get('database.default') === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }

    }
}
