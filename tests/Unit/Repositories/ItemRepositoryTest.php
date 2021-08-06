<?php
declare(strict_types=1);

namespace Tests\Unit\Repositories;


use App\Models\ItemPhoto;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use App\Models\Item;
use App\Repositories\Item\ItemEloquentRepository;
use Illuminate\Support\Facades\DB;

class ItemRepositoryTest extends TestCase
{

    use RefreshDatabase;

    protected $repo;

    protected $testItem;

    public function setUp(): void
    {
        parent::setUp();

        $item = new Item();
        $item->name = 'item_test';
        $item->price = 5000;
        $item->display_name = 'item_test_display';
        $item->type_id = 4;

        $this->testItem = $item;

        $this->repo = new ItemEloquentRepository();

        /* test data */
        Artisan::call('db:seed', ['--class' => 'ItemTypeTableSeeder']);
        Artisan::call('db:seed', ['--class' => 'ItemTableSeeder']);
        Artisan::call('db:seed', ['--class' => 'TagTableSeeder']);
        Artisan::call('db:seed', ['--class' => 'ItemTagTableSeeder']);
        Artisan::call('db:seed', ['--class' => 'ItemPhotoTableSeeder']);
    }

    public function tearDown(): void
    {
        Artisan::call('migrate:refresh');
        parent::tearDown();
    }

    /**
     * @ test
     */
    public function successInsert(){

        $this->repo->insertItem($this->testItem);

        //echo '  successInsert:  ' . $this->testItem->id;

        $this->assertDatabaseHas('items', [
            'id' => $this->testItem->id,
            'name' => $this->testItem->name,
            'price' => $this->testItem->price,
            'display_name' => $this->testItem->display_name,
            'type_id' => $this->testItem->type_id
        ]);

    }

    /**
     * @ test
     */
    public function successDeleteById(){

        $id = 5;

        $rtn = $this->repo->deleteItem($id);

        $this->assertTrue($rtn);

        $this->assertDatabaseMissing('items', [
            'id' => $id
        ]);
    }

    /**
     * @ test
     */
    public function successFindById(){

        $id = 2;

        $data = $this->repo->findItem($id);

        $this->assertIsObject($data);
        $this->assertEquals($id, $data->id);
        $this->assertEquals('item_test_2', $data->name);
        $this->assertEquals(2000, $data->price);
        $this->assertEquals('item_test_display_2', $data->display_name);
        $this->assertEquals(2, $data->type_id);

    }

    /**
     * @ test
     */
    public function successUpdateByItem(){

        $id = 5;

        $newItemName = 'item_test_5_new';
        $newItemPrice = 5500;
        $newItemDisplayName = 'test_display_new';
        $newTypeId = 4;

        $item = new Item();
        $item->id = $id;
        $item->price = $newItemPrice;
        $item->name = $newItemName;
        $item->display_name = $newItemDisplayName;
        $item->type_id = $newTypeId;

        $item->exists = true;

        $rtn = $this->repo->updateItem($item);

        $this->assertTrue($rtn);

        $this->assertDatabaseHas('items', [
            'id' => $id,
            'name' => $newItemName,
            'price' => $newItemPrice,
            'display_name' => $newItemDisplayName,
            'type_id' => $newTypeId
        ]);
    }

    /**
     * @ test
     */
    public function hasItemTypeName(){

        $id = 2;

        $item = $this->repo->findItem($id);

        $item_type_name =  $item->itemType->item_type_name;
        $expectedItemType = "name_2";

        $this->assertEquals($item_type_name, $expectedItemType);

    }

    /**
     * @ test
     */
    public function hasItemTags(){

        $id = 2;

        $tags = $this->repo->findItem($id)->tags()->orderBy('id')->get();

        $this->assertTrue($tags->count()  == 10);
        $this->assertEquals('tag4', $tags->get(3)->name);

    }

    /**
     * @ test
     */
    public function doNotHasItemTags(){

        $id = 5;

        $tags = $this->repo->findItem($id)->tags()->orderBy('id')->get();

        $this->assertTrue($tags->count()  == 0);
        $this->assertNull($tags->get(0));

    }

    /**
     * @ test
     */
    public function successAddTags(){

        $id = 5;

        $rtn = $this->repo->addTagsById($id, [2,4]);
        $this->assertTrue($rtn);

        $this->assertDatabaseHas('item_tag', [
            'item_id' => $id,
            'tag_id' => 2,
        ]);

        $this->assertDatabaseHas('item_tag', [
            'item_id' => $id,
            'tag_id' => 4,
        ]);

    }

    /**
     * @ test
     */
    public function successDelTags(){

        $id = 3;

        $rtn = $this->repo->delTagsById($id, [2]);
        $this->assertTrue($rtn);

        $this->assertDatabaseMissing('item_tag', [
            'item_id' => $id,
            'tag_id' => 2,
        ]);

    }

    /**
     * @ test
     */
    public function successSyncTags(){

        $id = 3;

        $rtn = $this->repo->syncTagsById($id, [2, 4]);
        $this->assertTrue($rtn);

        $this->assertDatabaseHas('item_tag', [
            'item_id' => $id,
            'tag_id' => 2,
        ]);

        $this->assertDatabaseHas('item_tag', [
            'item_id' => $id,
            'tag_id' => 4,
        ]);
        $this->assertDatabaseMissing('item_tag', [
            'item_id' => $id,
            'tag_id' => 3,
        ]);

    }


    /**
     * @ test
     */
    public function insertItemAndTags(){

        $tags = [1,3];

        DB::beginTransaction();
            $newItemId = $this->repo->insertItem($this->testItem);

            $this->assertEquals($this->testItem->id, $newItemId);

            $this->repo->addTags($this->testItem, $tags);

            DB::commit();

        $this->assertDatabaseHas('item_tag', [
            'item_id' => $this->testItem->id,
            'tag_id' => 1,
        ]);
        $this->assertDatabaseHas('item_tag', [
            'item_id' => $this->testItem->id,
            'tag_id' => 3,
        ]);

    }

    /**
     * @ test
     */
    public function addPhoto()
    {
        $item = Item::find(4);
        $photo = new ItemPhoto(['filename' => 'aaa.jpg' , 'order' => 1]);


        $rtn = $this->repo->addPhoto($item, $photo);

        echo $rtn;

        $this->assertDatabaseHas('item_photos', [
            'item_id' => $item->id,
            'order' => 1
        ]);
    }

    /**
     * @ test
     */
    public function delPhotos(){

        $item = Item::find(1);
        $arr = [];
        $photos = $item->photos()->get();
        foreach($photos as $photo){
            $arr[] = $photo->filename;
        }

        $rtn = $this->repo->delPhotos($item, $arr);

        $this->assertEquals($rtn, 3);
    }

}
