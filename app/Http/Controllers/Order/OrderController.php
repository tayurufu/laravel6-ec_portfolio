<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{

    public function showOrderHistoryPage()
    {
        $data = [
            'getOrderDetailUrl' => route('order.history.paginate', 1),
        ];
        return view('order.orderHistory', compact('data'));
    }

    public function getOrderHistories()
    {
        $userId = Auth::user()->id;
        $customerId = Customer::where(['user_id' => $userId])->first()->id;
        $orders = Order::where(['customer_id' => $customerId])->with('order_details')->orderBy('order_time')->get();
        return response()->json(['orders' => $orders]);
    }

    public function getOrderHistoriesPaginate($reqPage)
    {
        $page = ($reqPage < 0) ? 1 : $reqPage;
        $userId = Auth::user()->id;
        $customerId = Customer::where(['user_id' => $userId])->first()->id;
        $paginate = Order::where(['customer_id' => $customerId])->with('order_details')->with('order_details.item')->orderBy('order_time', 'desc')->paginate($page);
        return response()->json(['orders' => collect($paginate)]);
    }

    public function showOrderHistory($orderId)
    {
        $userId = Auth::user()->id;
        $customerId = Customer::where(['user_id' => $userId])->first()->id;

        $order = Order::with('order_details')->with('order_details.item')->find($orderId);

        if(!$order){
            return response()->json(['message' => '該当のデータが存在しません。'], 404);
        }

        if($order->customer_id !== $customerId){
            return response()->json(['message' => '権限エラーです。'], 403);
        }

        return response()->json(['order' => $order]);

    }

}
