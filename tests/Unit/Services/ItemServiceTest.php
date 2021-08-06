<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use App\Models\Item;
use App\Repositories\Item\ItemEloquentRepository;
use Illuminate\Support\Facades\DB;

use App\Services\ItemService;

class ItemServiceTest extends TestCase
{

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

        Artisan::call('cache:forget spatie.permission.cache');
        /* test data */
        Artisan::call('db:seed', ['--class' => 'DatabaseSeeder']);
    }

    public function tearDown(): void
    {
        Artisan::call('migrate:refresh');
        parent::tearDown();
    }
}
