<?php

namespace Tests\Feature\Acl;

use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class PermissionTest extends TestCase
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

    private function actingAsAdminApi()
    {
        $user = User::where(['email' => 'admin@gmail.com'])->first();
        return $this->actingAs($user);
    }

    /**
     * permmision一覧取得リクエスト(正常系)
     *
     * @test
     * @return void
     */
    public function permissionsGetOk()
    {
        $url = route('acl.permissions.get');

        $act = $this->actingAsAdminApi();
        $response = $act->get($url);

        $expectedCount = Permission::count();

        $response->assertOk();

        $content = json_decode($response->content(), true);
        $this->assertEquals($expectedCount, count($content['permissions']));

    }

    /**
     * Permissionの新規登録(正常系)
     *
     * @test
     */
    public function permissionCreateOk(){

        $url = route('acl.permission.post');

        $params = [
          'name' => 'test_permission',
          'guard_name' => 'api'
        ];

        $act = $this->actingAsAdminApi();
        $response = $act->post($url, $params);

        $response->assertOk()->assertJson([
            'permission' => $params
        ]);

    }

    /**
     * Permissionの新規登録 バリデーションエラー(異常系)
     *
     * @test
     */
    public function permissionCreateValidationNg(){

        $data = $this->getValidationTestData();

        foreach($data as $d)
        {
            $this->postRequest($d['params'], $d['message']);
        }

    }

    /**
     * Permissionの新規登録 存在するパーミッション(異常系)
     *
     * @test
     */
    public function permissionCreateDuplicateNg()
    {
        $url = route('acl.permission.post');

        $params = [
            'name' => 'test_permission',
            'guard_name' => 'api'
        ];

        Permission::create($params);

        $act = $this->actingAsAdminApi();
        $response = $act->post($url, $params);

        $response->assertStatus(400)->assertJson([
            'message' => 'すでに存在するパーミッションです。'
        ]);
    }


    /**
     * Permissionの更新(正常系)
     *
     * @test
     */
    public function permissionUpdateOk(){

        $createdData = Permission::create([
            'name' => 'test_permission',
            'guard_name' => 'web'
        ]);

        $url = route('acl.permission.put', $createdData->id);

        $params = [
            'name' => 'test_permission2',
            'guard_name' => 'api'
        ];

        $act = $this->actingAsAdminApi();
        $response = $act->put($url, $params);

        $response->assertOk()
            ->assertJson([
                'permission' => $params
            ]);

    }

    /**
     * Permissionの更新 バリデーションエラー(異常系)
     *
     * @test
     */
    public function permissionUpdateValidationNg(){

        $data = $this->getValidationTestData();
        foreach($data as $d)
        {
            $this->putRequest($d['params'], $d['message']);
        }

    }

    /**
     * Permissionの更新 存在しないパーミッション(異常系)
     *
     * @test
     */
    public function permissionUpdateNotFoundNg(){

        $createdData = Permission::create([
            'name' => 'test_permission',
            'guard_name' => 'web'
        ]);

        $url = route('acl.permission.put', $createdData->id);

        $createdData->delete();

        $params = [
            'name' => 'test_permission2',
            'guard_name' => 'api'
        ];

        $act = $this->actingAsAdminApi();
        $response = $act->put($url, $params);

        $response->assertStatus(400)
            ->assertJson([
                'message' => '存在しないパーミッションです。'
            ]);

    }


    /**
     * Permissionの新規登録 存在するパーミッション(異常系)
     *
     * @test
     */
    public function permissionUpdateDuplicateNg()
    {
        $params = [
            'name' => 'test_permission',
            'guard_name' => 'api'
        ];

        $createdData = Permission::create($params);


        $duplicatedParams = [
            'name' => 'test_permission2',
            'guard_name' => 'api'
        ];

        Permission::create($duplicatedParams);


        $url = route('acl.permission.put', $createdData->id);

        $act = $this->actingAsAdminApi();
        $response = $act->put($url, $duplicatedParams);

        $response->assertStatus(400)->assertJson([
            'message' => '他のパーミッションと値が重複しています。'
        ]);
    }

    /**
     * Permissionの削除(正常系)
     *
     * @test
     */
    public function permissionDeleteOk(){

        $createdData = Permission::create([
            'name' => 'test_permission',
            'guard_name' => 'web'
        ]);

        $url = route('acl.permission.delete', $createdData->id);

        $expectedCount = Permission::count();

        $act = $this->actingAsAdminApi();
        $response = $act->delete($url);

        $response->assertOk()->assertJson([
            'result' => 'ok',
            'permissionId' => $createdData->id
        ]);

        $this->assertEquals($expectedCount - 1, Permission::count());

    }

    /**
     * Permissionの削除 存在しないパーミッション(異常系)
     *
     * @test
     */
    public function permissionDeleteNotFoundNg(){

        $createdData = Permission::create([
            'name' => 'test_permission',
            'guard_name' => 'web'
        ]);

        $url = route('acl.permission.delete', $createdData->id);

        $createdData->delete();

        $act = $this->actingAsAdminApi();
        $response = $act->delete($url);

        $response->assertStatus(400)
            ->assertJson([
                'message' => '存在しないパーミッションです。'
            ]);

    }


    /**
     * admin権限以外でアクセスできない(異常系)
     *
     * @test
     */
    public function accessNg()
    {
        $expectedStatus = 401;

        $getAllUrl = route('acl.permissions.get');
        $createUrl = route('acl.permission.post', 1);
        $updateUrl = route('acl.permission.put', 1);
        $deleteUrl = route('acl.permission.delete', 1);

        $response = $this->get($getAllUrl);
        $response->assertStatus($expectedStatus);

        $response = $this->post($createUrl);
        $response->assertStatus($expectedStatus);

        $response = $this->get($updateUrl);
        $response->assertStatus($expectedStatus);

        $response = $this->get($deleteUrl);
        $response->assertStatus($expectedStatus);


        $expectedStatus = 403;

        $user = User::where(['email' => 'manager@gmail.com'])->first();
        $act = $this->actingAs($user);

        $response = $act->get($getAllUrl);
        $response->assertStatus($expectedStatus);

        $response = $act->post($createUrl);
        $response->assertStatus($expectedStatus);

        $response = $act->get($updateUrl);
        $response->assertStatus($expectedStatus);

        $response = $act->get($deleteUrl);
        $response->assertStatus($expectedStatus);

    }

    public function getValidationTestData()
    {
        return [
            [
                'params' => [
                    'name' => '',
                    'guard_name' => 'api'
                ],
                'message' => 'The name field is required.'
            ],
            [
                'params' => [
                    'name' => ['aaa'],
                    'guard_name' => 'api'
                ],
                'message' => 'The name must be a string.'
            ],
            [
                'params' => [
                    'name' => $this->makeRandStr(256),
                    'guard_name' => 'api'
                ],
                'message' => 'The name may not be greater than 255 characters.'
            ],
            [
                'params' => [
                    'name' => 'test',
                    'guard_name' => ['aaa']
                ],
                'message' => 'The guard name must be a string.'
            ],
            [
                'params' => [
                    'name' => 'aaa',
                    'guard_name' => $this->makeRandStr(256)
                ],
                'message' => 'The guard name may not be greater than 255 characters.'
            ],
        ];

    }

    private function postRequest($params, $expectedMessage)
    {
        $url = route('acl.permission.post');

        $act = $this->actingAsAdminApi();
        $response = $act->post($url, $params);

        $response->assertStatus(400)->assertJson([
            'message' => $expectedMessage
        ]);
    }

    private function putRequest($params, $expectedMessage)
    {
        $url = route('acl.permission.put', 1);

        $act = $this->actingAsAdminApi();
        $response = $act->put($url, $params);

        $response->assertStatus(400)->assertJson([
            'message' => $expectedMessage
        ]);
    }

    private function makeRandStr($length)
    {
        $str = array_merge(range('0', '9'), range('a', 'z'), range('A', 'Z'));
        $r_str = '';
        for ($i = 0; $i < $length; $i++) {
            $r_str .= $str[rand(0, count($str) - 1)];
        }
        return $r_str;
    }

}
