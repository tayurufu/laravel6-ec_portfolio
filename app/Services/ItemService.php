<?php
declare(strict_types=1);

namespace App\Services;


use App\Models\Item;
use App\Models\ItemPhoto;
use App\Models\Stock;
use App\Repositories\Item\ItemRepository;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ItemService
{
    private $repository;

    private $photosUrl = "/photos";

    /**
     * ItemController constructor.
     * @param ItemRepository $repository
     */
    public function __construct(ItemRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Itemをidで検索する
     * @param int $id
     * @return Item
     */
    public function findItem(int $id): ?Item
    {
        return $this->repository->findItem($id);
    }

    /**
     * Item全件取得  テスト用
     * @return array
     */
    public function findItems()
    {
        return $this->repository->findItems();
    }

    /**
     * ページ指定 検索
     * @param $page
     * @param array $whereParams
     * @param array $whereLikeParams
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function ItemPaginate($page, $whereParams = [], $whereLikeParams = [])
    {
        $perPage = 2;


        if(count($whereParams) == 0 && count($whereLikeParams) == 0){
            // 検索条件なし
            /*
             * "total":35,
             * "per_page":10,
             * "current_page":1,
             * "last_page":4,
             * "next_page_url":"http:\/\/localhost:8000\/json?page=2",
             * "prev_page_url":null,
             * "from":1,
             * "to":10,
             */
            return Item::orderBy('item_id', 'asc')->paginate($perPage);
        }

        $items = null;

        // 検索条件追加
        foreach($whereParams as $whereKey => $whereValue){
            $items = Item::addWhere($whereKey, $whereValue, false);
        }
        foreach($whereLikeParams as $whereKey => $whereValue){
            $items = Item::addWhere($whereKey, "%{$whereValue}%", true);
        }

        // 検索結果を返す
        return $items->orderBy('item_id', 'asc')->paginate($perPage);

    }

    /**
     * Itemを保存する
     * 新規追加も更新も可能
     * @param Item $item
     * @param array $tags
     * @param array $stock
     * @param array $photos
     * @param array $photoStatus
     * @return array
     * @throws \Exception
     */
    public function storeItem(Item $item, array $tags = [], $stock = [], $photos = [], $photoStatus = []): array
    {

        $filenames = [];
        $delFiles = [];

        // 保存用のファイル名作成
        foreach($photos as $photo){
            $filename = ItemPhoto::createRandomFileName(
                $item->item_id,
                $photo->getClientOriginalExtension()
            );
            array_push($filenames, $filename);

        }


        DB::beginTransaction();
        try {

            //Itemを保存
            $item = $this->repository->mergeItem($item);


            // タグがあれば保存
            if(count($tags) > 0){
                $this->repository->syncTags($item, $tags);
            }

            // 写真があったら追加
            $cnt = 0;
            foreach($filenames as $idx => $filename){

                if($photoStatus[$idx]['status'] == 1){
                    // 新規保存
                    $itemPhoto = new ItemPhoto();
                    $itemPhoto->item_id = $item->id;
                    $itemPhoto->filename = $filename;
                    $itemPhoto->order = $cnt;
                    $this->repository->addPhoto($item, $itemPhoto);

                    Storage::putFileAs($this->photosUrl, $photos[$idx], $filename);
                    $cnt++;
                } else if($photoStatus[$idx]['status'] == 2){
                    // 更新なし そのまま
                    $cnt++;
                } else if($photoStatus[$idx]['status'] == 3){

                    // 更新 古いのを消して、新規保存
                    $delItemPhoto = ItemPhoto::find($photoStatus[$idx]['id']);
                    if($delItemPhoto !== null){
                        $delFiles[] = $delItemPhoto->filename;
                        $delItemPhoto->delete();
                    }

                    $itemPhoto = new ItemPhoto();
                    $itemPhoto->item_id = $item->id;
                    $itemPhoto->filename = $filename;
                    $itemPhoto->order = $cnt;
                    $this->repository->addPhoto($item, $itemPhoto);
                    Storage::putFileAs($this->photosUrl, $photos[$idx], $filename);
                    $cnt++;

                } else if($photoStatus[$idx]['status'] == 4){
                    // 削除
                    $delItemPhoto = ItemPhoto::find($photoStatus[$idx]['id']);
                    $delFiles[] = $delItemPhoto->filename;
                    $delItemPhoto->delete();
                    $cnt++;
                }

            }

            //stock
            if($item->stock === null){
                $item->stock()->save(new Stock($stock));
            } else {
                $item->stock()->update($stock);
            }


            DB::commit();

            // DBから削除したファイル名をストレージから削除
            foreach($delFiles as $delFile){
                Storage::delete($this->photosUrl . "/". $delFile);
            }

        } catch (\Exception $e) {
            DB::rollback();
            //foreach($filenames as $filename){
            //    Storage::delete($this->photosUrl . "/". $filename);
            //}
            throw $e;
        }

        $item->refresh();
        return $this->returnArray(true, '', $item);
    }

    /**
     * Item削除
     * @param string $itemName
     * @return array
     * @throws \Exception
     */
    public function deleteItem(string $itemName): array
    {
        $item = $this->repository->findItemByItemId($itemName);

        if($item == null){
            return $this->returnArray(false, 'not found', null);
        }

        DB::beginTransaction();
        try {

            $filenames = $item->photos()->pluck('filename')->toArray();

            $this->repository->deleteItem($item->id);
            DB::commit();

            foreach($filenames as $filename) {
                Storage::delete($this->photosUrl . "/". $filename);
            }

        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }

        return $this->returnArray(true, 'delete complete!!', null);
    }


    /**
     * Itemの写真データ取得
     * @param $filename
     * @return array | null
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function getItemPhoto($filename) {

        if($filename !== null){
            $data = [];

            try{
                $filepath = $this->photosUrl . "/". $filename;
                // S3の場合はlocalにキャッシュする
                if (Config::get('filesystems.default') === 's3') {

                     if(!Storage::disk('photos')->exists($filepath)){
                         // photosに持っていない
                         if(!Storage::exists($filepath)){
                             $filepath = $this->photosUrl . "/". 'no_image.png';
                         } else {
                             //キャッシュさせる
                             Storage::disk('photos')->put($filepath, Storage::get($filepath));
                         }
                     }

                     $data['photo'] = Storage::disk('photos')->get($filepath);
                     $data['Content-Type'] = Storage::disk('photos')->mimeType($filepath);
                     $data['Content-Length'] = Storage::disk('photos')->size($filepath);

                } else {
                    if(!Storage::exists($filepath)){
                        $filepath = $this->photosUrl . DIRECTORY_SEPARATOR . 'no_image.png';
                    }

                    $data['photo'] = Storage::get($filepath);
                    $data['Content-Type'] = Storage::mimeType($filepath);
                    $data['Content-Length'] = Storage::size($filepath);

                }

                return $data;

            } catch(\Exception $e){
                Log::error('file not found : '. $filename . "\n" . $e->getMessage());
                return null;
            }

        }
        return null;
    }

    /**
     * 戻り値
     * @param bool $rtnBool
     * @param string $msg
     * @param object|null $obj
     * @return array
     */
    private function returnArray(bool $rtnBool, string $msg = '', object $obj = null) : array{
        return [$rtnBool, $msg, $obj];
    }
}
