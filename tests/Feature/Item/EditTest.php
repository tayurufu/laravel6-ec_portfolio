<?php

namespace Tests\Feature\Item;

use App\Models\Item;
use App\Models\ItemPhoto;
use App\Models\ItemTag;
use App\Models\OrderDetail;
use App\Models\Stock;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EditTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        Artisan::call('config:clear');
        Artisan::call('cache:forget spatie.permission.cache');
        Artisan::call('db:seed', ['--class' => 'DatabaseSeeder']);
    }

    public function tearDown(): void
    {

        $photos = ItemPhoto::select('filename')->get();
        foreach($photos as $photo){
            $name = $photo->filename;
            Storage::delete("/photos/". $name);
        }

        Artisan::call('migrate:refresh');
        parent::tearDown();
    }

    private function actingAsAdminApi()
    {
        $user = User::where(['email' => 'admin@gmail.com'])->first();
        return $this->actingAs($user);
    }


    /**
     * 画面遷移
     * @ test
     */
    public function initViewTest(){

        $url = route('item.edit');
        $act = $this->actingAsAdminApi();
        $response = $act->get($url);
        $response->assertOk();

    }

    /**
     * Item削除 正常
     * @ test
     */
    public function deleteItemOK(){

        $id = 1;
        $name = 'item_test1';

        $url = route('item.delete', $name);

        $expCnt = OrderDetail::where(['item_name' => $name])->count();

        $act = $this->actingAsAdminApi();
        $response = $act->delete($url);
        $response->assertOk();

        //削除したItemに関連するデータが全て消えているかチェック
        $this->assertDatabaseMissing('items', ['id' => $id, 'name' => $name]);

        $data = ItemPhoto::where(['item_name' => $name])->first();
        $this->assertNull($data);
        $data = ItemTag::where(['item_name' => $name])->first();
        $this->assertNull($data);
        $data = Stock::where(['item_name' => $name])->first();
        $this->assertNull($data);

        $cnt = OrderDetail::where(['item_name' => $name])->count();
        $this->assertEquals($expCnt, $cnt);
    }

    /**
     * Item削除 存在しないItem
     * @ test
     */
    public function deleteItemNG(){

        $name = 'item_test99';

        $url = route('item.delete', $name);

        $act = $this->actingAsAdminApi();
        $response = $act->delete($url);
        $response->assertStatus(400)->assertJson(['message' => 'not found']);

    }

    /**
     * Item新規登録
     * @test
     */
    public function insertItemOK(){

        $url = route('item.store');

        $params = [
            'item_name' => 'test12345',
            'item_display_name' => 'iygoyboiybicl',
            'item_price' => '9999',
            'item_type' => "3",
            'item_description' => 'test description',
            'tags' => ["1","3","5"],
            'photoStatus' => ['1', '0', '1', '0'],
            'photoIds' => [null, null, null, null],
            'photos' => [
                $this->createDummyFile(),
                $this->createDummyFile(),
                $this->createDummyFile(),
                $this->createDummyFile(),
            ],
            'item_qty' => '10',
            'item_location' => '2'

        ];

        $act = $this->actingAsAdminApi();
        $response = $act->post($url, $params);


        $content = json_decode($response->content(), true);
        $id = $content['item']['id'];

        $response->assertOk()
            ->assertJson([
            'item' => [
                'id' => $id,
                'name' => $params['item_name'],
                'display_name' => $params['item_display_name'],
                'price' => (int)$params['item_price'],
                'type_id' => (int)$params['item_type'],
                'description' => $params['item_description'],
                'tags' => [
                    ['tag_id' => 1, 'tag_name' => 'tag1'],
                    ['tag_id' => 3, 'tag_name' => 'tag3'],
                    ['tag_id' => 5, 'tag_name' => 'tag5']
                ],
                'stock' => [
                    'qty' => '10',
                    'location' => '2'
                ]
            ]
        ]);


        $this->assertDatabaseHas('items' , [
            'id' => $id,
            'name' => $params['item_name'],
            'display_name' => $params['item_display_name'],
            'price' => (int)$params['item_price'],
            'type_id' => (int)$params['item_type'],
            'description' => $params['item_description'],
        ]);
        $cnt = ItemPhoto::where(['item_name' => $params['item_name']])->count();
        $this->assertEquals(2, $cnt);

        foreach($params['tags'] as $tag){
            $this->assertDatabaseHas('item_tag' , ['item_name' => $params['item_name'], 'tag_id' => (int)$tag]);
        }

        $data = Stock::where(['item_name' => $params['item_name']])->first();
        $this->assertNotNull($data);

        $photos = ItemPhoto::where(['item_name' => $params['item_name']])->select('filename')->get();
        foreach($photos as $photo){
            $this->assertTrue(Storage::exists("/photos/". $photo->filename));
        }

    }

    private function createDummyFile(){
        return UploadedFile::fake()->image('avatar.jpg', 500, 500)
            ->size(100);
    }


}
