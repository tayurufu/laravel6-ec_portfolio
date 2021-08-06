<?php

namespace Tests\Feature\Item;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ListTest extends TestCase
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

        $url = route('item.index');
        $response = $this->get($url);
        $response->assertOk();

    }



}
