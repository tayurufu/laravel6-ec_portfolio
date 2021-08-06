<?php

use App\Models\StockLocation;
use Illuminate\Database\Seeder;

class StockLocationTableSeeder extends Seeder
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
        StockLocation::truncate();

        //
        for($i = 0; $i < 5; $i++) {
            StockLocation::create([
                'location_id' =>  sprintf('%04d', ($i + 1)),
                'name' => 'location_'. $i,
            ]);
        }

        if (Config::get('database.default') === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }
}
