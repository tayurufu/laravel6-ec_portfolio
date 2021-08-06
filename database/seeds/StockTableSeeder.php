<?php

use App\Models\Stock;
use Illuminate\Database\Seeder;

class StockTableSeeder extends Seeder
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
        Stock::truncate();

        $items = App\Models\Item::all();

        foreach($items as $k => $item){
            Stock::create([
                'item_id' => $item->item_id,
                'location_id' => 1,
                'stock' => 100,
            ]);
        }

        if (Config::get('database.default') === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }
}
