<?php

namespace App\Http\Controllers\Item;

use App\Http\Controllers\Controller;
use App\Http\Requests\Item\ItemDetailRequest;
use App\Http\Requests\Item\ItemPaginateRequest;
use App\Http\Requests\Item\ItemStoreRequest;
use App\Models\Item;
use App\Models\Stock;
use App\Models\StockLocation;
use App\Repositories\ItemType\ItemTypeRepository;
use App\Repositories\Tag\TagRepository;
use App\Services\CartService;
use App\Services\ItemService;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

use App\Http\Resources\Item\ItemResource;
use App\Http\Resources\Item\ItemPaginateResource;

class ItemController extends Controller
{
    private $service;
    private $tagRepository;
    private $itemTypeRepository;
    private $cartService;

    /**
     * ItemController constructor.
     * @param ItemService $service
     * @param TagRepository $tagRepository
     * @param ItemTypeRepository $itemTypeRepository
     */
    public function __construct(ItemService $service, CartService $cartService, TagRepository $tagRepository, ItemTypeRepository $itemTypeRepository)
    {
        $this->service = $service;
        $this->cartService = $cartService;
        $this->tagRepository = $tagRepository;
        $this->itemTypeRepository = $itemTypeRepository;
    }


    /**
     * Item一覧を表示
     * Display a listing of the resource.
     *
     * @return Factory|\Illuminate\View\View
     */
    public function index()
    {

        $itemTypes = $this->itemTypeRepository->getAll();

        $canEdit  = $this->canEditItem();

        $data = [
            'getItemsUrl' => route('items.get'),
            'createItemUrl' => $canEdit ? route('item.edit') : "",
            'canEdit' => $canEdit,
            'itemTypes'=> $itemTypes,
        ];
        return view('item.index', compact('data'));
    }

    /**
     * Item編集権限チェック
     * @return bool
     */
    private function canEditItem(): bool
    {
        $canEdit = false;

        $user = Auth::user();

        if($user !== null && $user->can('edit_item')){
            $canEdit = true;
        }

        return $canEdit;
    }

    /**
     * ページ指定のItem Json取得
     * @return array
     */
    public function getPaginateItem(ItemPaginateRequest $request){

        $searchKeys = [];
        $searchLikeKeys = [];
        $page = \request()->get('page') ?? 1;
        $searchItemType = trim($request->searchItemType ?? '');
        $searchName = trim($request->searchName ?? '');
        if($searchItemType != ''){
            $searchKeys['type_id'] = $searchItemType;
        }
        if($searchName != ''){
            $searchLikeKeys['name'] = $searchName;
        }

        $items = new ItemPaginateResource($this->service->ItemPaginate($page, $searchKeys, $searchLikeKeys));

        return ['items' => $items];

    }


    /**
     * api Item登録・更新
     * @param ItemStoreRequest $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function store(ItemStoreRequest $request)
    {

        $item = new Item([
            'id' => (int)$request->input('id'),
            'item_id' => $request->input('item_id'),
            'name' => $request->input('item_name'),
            'price' => (int)$request->input('item_price'),
            'type_id' => (int)$request->input('item_type'),
            'description' => $request->input('item_description'),
        ]);


        $qty = (int)$request->input('item_qty') ?? 0 ;
        $location = (int)$request->input('item_location') ?? "";
        $stock = ['item_id' => $item->id, 'stock' => $qty, 'location_id' => $location];


        $inputTags = $request->input('tags') ?? [];
        $tags = [];
        foreach($inputTags as $tag){
            $tags[] = (int)$tag;
        }

        $photos = $request->file('photos');
        $photoStatus = $request->input('photoStatus');
        $photoIds = $request->input('photoIds');

        $idx = 0;
        $photoObj = [];
        foreach($photoStatus as $status){
            $photoObj[$idx]['status'] = $photoStatus[$idx];
            $photoObj[$idx]['id'] = $photoIds[$idx];
            $idx++;
        }


        $array = $this->service->storeItem($item, $tags, $stock, $photos, $photoObj);

        return new JsonResponse(
            [
                "result" => "ok",
                "message" => "ok",
                "item" => new ItemResource($array[2]),
                "redirectUrl" => route('item.edit', [$item->id])
            ]);
    }

    /**
     * 商品詳細画面表示
     * @param Request $request
     * @param $id
     * @return Factory|\Illuminate\View\View
     */
    public function detail(ItemDetailRequest $request)
    {
        $id = (int)$request->id;
        $item = $this->service->findItem($id);

        $hasCart = false;

        if($this->cartService->hasItemId($id))
        {
            $hasCart = true;
        }

        $data = [
            'addCartUrl' => route('item.cart.add', $item->id),
            'removeCartUrl' => route('item.cart.remove', $item->id),
            'backPageUrl' => route('item.index'),
            'item' => new ItemResource($item),
            'cart' => $this->cartService->getCart($id),
            'hasCart' => $hasCart,
            'isLogin' => Auth::check()
        ];

        return view('item.detail', compact('data'));
    }

    /**
     * Item更新画面
     * @param string $id
     * @return Factory|\Illuminate\View\View
     */
    public function edit(string $id = "")
    {

        $item = null;

        if(trim($id) !== ""){
            $item = $this->service->findItem((int)$id);
            if(!$item){
                return redirect(route('item.index'));
            }
            $item = new ItemResource($item);
        }

        if($item === null){
            $mode = "create";
        } else {
            $mode = "update";
        }

        $tags = $this->tagRepository->getAll()->toArray();

        $itemTypes = $this->itemTypeRepository->getAll()->toArray();

        $stockLocations = StockLocation::all();

        $data = [
            'item' => $item,
            'mode' => $mode,
            'tags' => $tags,
            'stockLocations' => $stockLocations,
            'itemTypes' => $itemTypes,
            'backPageUrl' => route('item.index'),
            'storeUrl' => route('item.store'),
            'deleteUrl' => ($mode === "create") ? "" : route('item.delete', [ $item['id']]),
            'dummyImage' => '/no_image.png',
        ];
        return view('item.edit', compact('data'));

    }


    /**
     * Item名で削除する
     * @param int $id
     * @return JsonResponse
     * @throws \Exception
     */
    public function delete(int $id)
    {
        $array = $this->service->deleteItem($id);

        if($array[0]){
            return new JsonResponse(
                [
                    "result" => "ok",
                    "message" => "ok",
                    "redirectUrl" => route('item.index')
                ]);
        }

        return new JsonResponse(
            [
                "result" => "ng",
                "message" => $array[1] ?? "ng",
                "redirectUrl" => route('item.index')
            ],400);
    }

    /**
     * Itemの写真データ取得
     * @param $filename
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response|null
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function getItemPhoto($filename){

        $data = $this->service->getItemPhoto($filename);

        if($data === null){
            return response(["not found"], 404);
        }

        return response($data['photo'], 200)
            ->header('Content-Type',$data['Content-Type'])
            ->header('Content-Length', $data['Content-Length']);
    }


//テスト用
    public function showItemJson(){
        return ItemResource::collection($this->service->findItems());
    }


}
