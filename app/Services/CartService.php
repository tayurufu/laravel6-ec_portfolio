<?php
declare(strict_types=1);

namespace App\Services;


use App\Models\Item;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Stock;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CartService
{

    public function getCartAll(){
        return \request()->session()->get('cart', []);
    }

    public function getCart($id){
        $cart = \request()->session()->get('cart', []);

        if($cart === null){
            return null;
        }

        foreach ($cart as $k => $v){
            if($v['id'] === $id){
                return $v;
            }
        }

        return null;
    }

    public function hasItemId($id){

        if($this->getCart($id) !== null){
            return true;
        } else {
            return false;
        }

    }

    public function addCart($id, $qty){

        $newData = [
            'id' => $id,
            'qty' => $qty
        ];

        $cart = \request()->session()->get('cart', []);

        if($cart === null){
            $cart = [];
        }

        $cart[] = $newData;

        \request()->session()->put('cart', $cart);
    }

    public function deleteCart($id){
        $cart = \request()->session()->get('cart', []);

        if($cart === null){
            return;
        }

        $newCart = array_filter($cart, function($element) use ($id){
            return $element['id'] !== $id;
        });

        \request()->session()->put('cart', $newCart);

    }

    public function deleteCartByArray($deleteObjectArray, $assoc = false){
        foreach ($deleteObjectArray as $v){
            $deleteId = ($assoc) ? $v['id'] : $v;
            $this->deleteCart($deleteId);
        }
    }

    public function deleteCartAll(){
        \request()->session()->put('cart', []);
    }

    public function getCartItemsWithData($cart){
        $myCartItems = [];
        foreach($cart as $k => $v){

            $item = Item::where(['id' => $v['id']])->with('stock')->first();

            if(!$item){
                continue;
            }

            $myCartItems[] = (object)[
                'id' => $item->id,
                'item_id' => $item->item_id,
                'name' => $item->name,
                'price' => $item->price,
                'thumbnail' => $item->thumbnail,
                'detailUrl' => route('item.detail', ['id' => $item->id]),
                'qty' => (int)$v['qty'],
                'hasStock' => $item->stock->stock > 0 && $item->stock->stock >= $v['qty']
            ];

        }

        return $myCartItems;
    }

    public function buy($customerId, $items){

        DB::beginTransaction();
        try {
            $totalPrice = 0;

            $order = Order::create([
                'order_id' => uniqid((string)$customerId, true),
                'customer_id' => $customerId,
                'order_state' => '0',
                'order_time' => Carbon::now()
            ]);

            $orderDetailSeq = 1;
            foreach($items as $k => $v){
                $id = $v['id'];
                $qty = $v['qty'];

                $this->minusStockAuto($id, $qty);

                $unitPrice = Item::where(['id' => $id])->first()->price;
                $sumPrice = $unitPrice * (int)$qty;
                $totalPrice += $sumPrice;

                OrderDetail::create([
                    'order_id' => $order->id,
                    'order_detail_seq' => $orderDetailSeq,
                    'item_id' => $id,
                    'item_qty' => $qty,
                    'unit_price' => $unitPrice,
                    'sum_price' => $sumPrice,
                ]);

                $orderDetailSeq++;
            }

            $order->refresh();
            $order->total_price = $totalPrice;
            $order->save();

            DB::commit();

            $order->refresh();
            return $order;

        } catch (\Exception $e) {
            DB::rollback();

            throw $e;

        }
    }

    private function minusStockAuto($id, $qty){

        $res = Stock::where(['item_id' => $id])
            ->where('stock', '>=', $qty)
            ->decrement('stock', $qty);

    }
}
