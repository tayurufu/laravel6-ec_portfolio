<?php

namespace Tests\Feature\Item;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use Illuminate\Support\Facades\Artisan;

class DetailTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        Artisan::call('cache:forget spatie.permission.cache');
        Artisan::call('db:seed', ['--class' => 'DatabaseSeeder']);
    }

    public function tearDown(): void
    {
        Artisan::call('migrate:refresh');
        parent::tearDown();
    }

    /**
     * 画面遷移
     * @test
     */
    public function initViewTest(){

        $url = route('item.detail', 'item_test1');
        $response = $this->get($url);
        $response->assertOk();

    }

    /**
     * カートにItem追加
     * @test
     */
    public function addCartItemOK(){

        $url = route('item.cart.add', 'item_test1');

        $carts = [
            'test' => ['qty' => '3']
        ];
        $expectedCarts = [
            'test' => ['qty' => '3'],
            'item_test1'=> ['qty' => '10']
        ];

        $params = ['qty' => '10'];

        $response = $this->withSession(['cart' => $carts])->post($url, $params);
        $response->assertOk();

        $response->assertSessionHas('cart', $expectedCarts);

    }

    /**
     * カートにItem追加 セッションが空の場合
     * @test
     */
    public function addCartItemOKEmpty(){

        $url = route('item.cart.add', 'item_test1');

        $expectedCarts = [
            'item_test1'=> ['qty' => '10']
        ];

        $params = ['qty' => '10'];

        $response = $this->post($url, $params);
        $response->assertOk();

        $response->assertSessionHas('cart', $expectedCarts);

    }

    /**
     * カートにItem追加 すでにカートに存在する場合
     * @test
     */
    public function addCartItemDuplicateItem(){
        $url = route('item.cart.add', 'item_test1');

        $carts = [
            'item_test1' => ['qty' => '3']
        ];

        $params = ['qty' => '11'];

        $response = $this->withSession(['cart' => $carts])->post($url, $params);
        $response->assertStatus(400)->assertJson(['message' => 'すでにカートに存在します。一度削除してください。']);

        $response->assertSessionHas('cart', $carts);
    }

    /**
     * カートにItem追加 在庫数超過
     * @test
     */
    public function addCartItemOverQty(){
        $url = route('item.cart.add', 'item_test1');

        $carts = [
            'test' => ['qty' => '3']
        ];

        $params = ['qty' => '11'];

        $response = $this->withSession(['cart' => $carts])->post($url, $params);
        $response->assertStatus(400)->assertJson(['message' => '在庫数を上回っています。']);

        $response->assertSessionHas('cart', $carts);
    }

    /**
     * カートからItem削除
     * @test
     */
    public function removeCartItemOK(){

        $url = route('item.cart.remove', 'item_test1');

        $carts = [
            'test' => ['qty' => '3'],
            'item_test1'=> ['qty' => '10']
        ];

        $expectedCarts = [
            'test' => ['qty' => '3']
        ];

        $params = [];

        $response = $this->withSession(['cart' => $carts])->post($url, $params);
        $response->assertOk();

        $response->assertSessionHas('cart', $expectedCarts);
    }

    /**
     * カートからItem削除 カートに存在しない場合
     * @test
     */
    public function removeCartNotExistsItem(){
        $url = route('item.cart.remove', 'XXXXX');

        $carts = [
            'test' => ['qty' => '3'],
            'item_test1'=> ['qty' => '10']
        ];

        $params = [];

        $response = $this->withSession(['cart' => $carts])->post($url, $params);
        $response->assertOk();

        $response->assertSessionHas('cart', $carts);
    }

    /**
     * カートからItem削除 セッションに存在しない場合
     * @test
     */
    public function removeCartEmptySession(){
        $url = route('item.cart.remove', 'XXXXX');

        $params = [];

        $response = $this->post($url, $params);
        $response->assertOk();

        $response->assertSessionMissing('cart');
    }
}
