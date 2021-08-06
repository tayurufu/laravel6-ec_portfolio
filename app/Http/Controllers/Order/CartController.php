<?php

namespace App\Http\Controllers\Order;

use App\Events\PurchaseEvent;
use App\Http\Controllers\Controller;
use App\Repositories\Tag\TagRepository;
use App\Services\CartService;
use App\Services\ItemService;
use DB;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\Stock;
use App\Models\Order;
use App\Models\OrderDetail;
use Auth;
use App\Models\Customer;
use Illuminate\Support\Facades\Log;

use Carbon\Carbon;

class CartController extends Controller
{

    private $itemService;
    private $cartService;

    /**
     * ItemController constructor.
     * @param ItemService $itemService
     * @param TagRepository $tagRepository
     */
    public function __construct(ItemService $itemService, CartService $cartService)
    {
        $this->itemService = $itemService;
        $this->cartService = $cartService;
    }


    public function showMyCart(Request $request){

        $userid = Auth::user()->id ?? "XXXX";

        $data = (object) [
            'getCartItemsUrl' => route('order.myCart.items'),
            'removeCartItemsUrl' => route('order.myCart.removeCartItems'),
            'buyCartItemsUrl' => route('order.myCart.buyCartItems'),
            'backUrl' => route('item.index'),
            'getCustomerUrl' => route('customers.show',  $userid ),
            'editCustomerUrl' => route('customer.edit',  $userid )
        ];

        return view('order.myCart', compact('data'));
    }

    public function getCartItems(Request $request){

        $cart = $this->cartService->getCartAll();

        if($cart === null || count($cart) == 0){
            $data = (object)[
                'myCartItems' =>  null
            ];

            return response()->json($data);

        }

        $data = (object)[
            'myCartItems' => $this->cartService->getCartItemsWithData($cart)
        ];

        return response()->json($data);
    }

    public function removeCartItems(Request $request){

        $removeCartItemsData = $request->get('removeCartItems');

        $this->cartService->deleteCartByArray($removeCartItemsData, false);

        return response()->json(['newCartItems'=> $this->cartService->getCartAll()]);

    }

    public function buyCartItems(Request $request){

        $buyCartItemsData = $request->get('buyCartItems');

        $customer = Customer::where(['user_id' => Auth::id()])->first();
        if(!$customer){
            return response()->json(['result' => 'ng'], 404);
        }
        $customer_id = $customer->id ;
        $order = $this->cartService->buy($customer_id, $buyCartItemsData);

        $this->cartService->deleteCartByArray($buyCartItemsData, true);
        $newCartItems = $this->cartService->getCartAll();

        session()->put('cart', $newCartItems);

        //メール送信
        // エラー発生時、クライアントには正常終了とみなす
        try{
            event(new PurchaseEvent(Auth::user(), $order));
        }catch(\Exception $e){
            Log::error($e->getMessage() . "\n" . $e->getTraceAsString());
        }

        return response()->json(['newCartItems'=> $newCartItems]);
    }

    /**
     * カートに追加
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function addCartItem(Request $request, int $id)
    {

        $validator = \Validator::make($request->all(), [
            'qty' => 'required|integer'
        ]);

        if($validator->fails()){
            return new JsonResponse(
                [
                    "message" => $validator->messages()->first()
                ],
                400 );
        }

        $qty = (int)$request->input('qty');

        $item = $this->itemService->findItem($id);
        if($item === null){
            return response()->json(['message' => '存在しない商品です。'], 400);
        }

        if($this->cartService->hasItemId($id))
        {
            return response()->json(['message' => 'すでにカートに存在します。一度削除してください。'], 400);
        }

        $stock = Stock::where(['item_id' => $id])->first();
        if($stock === null || $stock->stock < $qty){
            return response()->json(['message' => '在庫数を上回っています。'], 400);
        }

        $this->cartService->addCart($id, $qty);

        return ['result' => 'ok'];

    }

    /**
     * カートから削除
     * @param Request $request
     * @param string $id
     * @return array|Factory|\Illuminate\View\View
     */
    public function removeCartItem(Request $request, int $id)
    {
        $this->cartService->deleteCart($id);
        return ['result' => 'ok'];
    }


}
