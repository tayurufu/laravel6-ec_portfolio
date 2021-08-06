<?php

use App\Models\Order;
use App\Models\OrderDetail;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class OrderTableSeeder extends Seeder
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
        OrderDetail::truncate();
        Order::truncate();

        $order = Order::create([
            'order_id' => uniqid('', true),
            'customer_id' => 1,
            'order_state' => 0,
            'order_time' => Carbon::now(),
            'send_date' => '2022/01/01'
        ]);

        $totalPrice = 0;
        for($i = 1; $i <= 5; $i++) {

            $item = \App\Models\Item::inRandomOrder()->first();

            $qty = Faker\Factory::create('ja_JP')->randomNumber(1);

            $orderDetail = OrderDetail::create([
                'order_id' => $order->id,
                'order_detail_seq' => $i,
                'item_id' => $item->id,
                'item_qty' => $qty,
                'unit_price' => $item->price,
                'sum_price' => $item->price * $qty
            ]);

            $totalPrice += $orderDetail->sum_price;
        }

        $order->total_price = $totalPrice;
        $order->save();

        if (Config::get('database.default') === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }
}
