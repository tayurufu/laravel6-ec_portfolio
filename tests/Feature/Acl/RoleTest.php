<?php

namespace Tests\Feature\Acl;

use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        Artisan::call('cache:forget spatie.role.cache');
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
     * role一覧取得リクエスト(正常系)
     *
     * @test
     * @return void
     */
    public function rolesGetOk()
    {
        $url = route('acl.roles.get');

        $act = $this->actingAsAdminApi();
        $response = $act->get($url);

        $expectedCount = Role::count();

        $response->assertOk();

        $content = json_decode($response->content(), true);
        $this->assertEquals($expectedCount, count($content['roles']));

    }

    /**
     * Roleの新規登録(正常系)
     *
     * @test
     */
    public function roleCreateOk(){

        $url = route('acl.role.post');

        $params = [
            'name' => 'test_role',
            'guard_name' => 'api',
            'permissions' => [1,3,5]
        ];

        $act = $this->actingAsAdminApi();
        $response = $act->post($url, $params);

        $response->assertOk()->assertJson([
            'role' => $params
        ]);

    }



    /**
     * Roleの新規登録 存在するロール(異常系)
     *
     * @test
     */
    public function roleCreateDuplicateNg()
    {
        $url = route('acl.role.post');

        $params = [
            'name' => 'test_role',
            'guard_name' => 'api',
            'permissions' => [1,3,5]
        ];

        $role = Role::create([
            'name' => 'test_role',
            'guard_name' => 'api'
        ]);
        $role->permissions()->sync([1,3,5]);

        $act = $this->actingAsAdminApi();
        $response = $act->post($url, $params);

        $response->assertStatus(400)->assertJson([
            'message' => 'すでに存在するロールです。'
        ]);
    }


    /**
     * Roleの更新(正常系)
     *
     * @test
     */
    public function roleUpdateOk(){

        $createdData = Role::create([
            'name' => 'test_role',
            'guard_name' => 'web'
        ]);

        $createdData->permissions()->sync([1,3,5]);

        $url = route('acl.role.put', $createdData->id);

        $params = [
            'name' => 'test_role2',
            'guard_name' => 'api',
            'permissions' => [2,4,7]
        ];

        $act = $this->actingAsAdminApi();
        $response = $act->put($url, $params);

        $response->assertOk()
            ->assertJson([
                'role' => $params
            ]);

    }

    /**
     * Roleの更新 存在しないロール(異常系)
     *
     * @test
     */
    public function roleUpdateNotFoundNg(){

        $createdData = Role::create([
            'name' => 'test_role',
            'guard_name' => 'web'
        ]);

        $url = route('acl.role.put', $createdData->id);

        $createdData->delete();

        $params = [
            'name' => 'test_role2',
            'guard_name' => 'api'
        ];

        $act = $this->actingAsAdminApi();
        $response = $act->put($url, $params);

        $response->assertStatus(400)
            ->assertJson([
                'message' => '存在しないロールです。'
            ]);

    }


    /**
     * Roleの更新 存在するロール(異常系)
     *
     * @test
     */
    public function roleUpdateDuplicateNg()
    {
        $params = [
            'name' => 'test_role',
            'guard_name' => 'api'
        ];

        $createdData = Role::create($params);


        $duplicatedParams = [
            'name' => 'test_role2',
            'guard_name' => 'api',
            'permissions' => [5]
        ];

        Role::create([
            'name' => 'test_role2',
            'guard_name' => 'api'
        ]);


        $url = route('acl.role.put', $createdData->id);

        $act = $this->actingAsAdminApi();
        $response = $act->put($url, $duplicatedParams);

        $response->assertStatus(400)->assertJson([
            'message' => '他のロールと値が重複しています。'
        ]);
    }

    /**
     * Roleの更新 パーミッションの更新のみ(正常系)
     * @test
     */
    public function roleUpdateOnlyPermissionOK()
    {
        $params = [
            'name' => 'test_role',
            'guard_name' => 'api',

        ];

        $createdData = Role::create($params);
        $createdData->permissions()->sync([1,3,5]);

        $updateParams = [
            'name' => 'test_role',
            'guard_name' => 'api',
            'permissions' => [2,8]
        ];

        $url = route('acl.role.put', $createdData->id);

        $act = $this->actingAsAdminApi();
        $response = $act->put($url, $updateParams);

        $response->assertOk()
            ->assertJson([
                'role' => $updateParams
            ]);
    }

    /**
     * Roleの削除(正常系)
     *
     * @test
     */
    public function roleDeleteOk(){

        $createdData = Role::create([
            'name' => 'test_role',
            'guard_name' => 'web'
        ]);

        $url = route('acl.role.delete', $createdData->id);

        $expectedCount = Role::count();

        $act = $this->actingAsAdminApi();
        $response = $act->delete($url);

        $response->assertOk()->assertJson([
            'result' => 'ok',
            'roleId' => $createdData->id
        ]);

        $this->assertEquals($expectedCount - 1, Role::count());

    }

    /**
     * Roleの削除 存在しないロール(異常系)
     *
     * @test
     */
    public function roleDeleteNotFoundNg(){

        $createdData = Role::create([
            'name' => 'test_role',
            'guard_name' => 'web'
        ]);

        $url = route('acl.role.delete', $createdData->id);

        $createdData->delete();

        $act = $this->actingAsAdminApi();
        $response = $act->delete($url);

        $response->assertStatus(400)
            ->assertJson([
                'message' => '存在しないロールです。'
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

        $getAllUrl = route('acl.roles.get');
        $createUrl = route('acl.role.post', 1);
        $updateUrl = route('acl.role.put', 1);
        $deleteUrl = route('acl.role.delete', 1);

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


    /**
     * Roleの新規登録 バリデーションエラー(異常系)
     *
     * @test
     */
    public function roleCreateValidationNg(){

        $data = $this->getValidationTestData();

        foreach($data as $d)
        {
            $this->postRequest($d['params'], $d['message']);
        }

    }

    /**
     * Roleの更新 バリデーションエラー(異常系)
     *
     * @test
     */
    public function roleUpdateValidationNg(){

        $data = $this->getValidationTestData();

        foreach($data as $d)
        {
            $this->putRequest($d['params'], $d['message']);
        }

    }

    private function postRequest($params, $expectedMessage)
    {
        $url = route('acl.role.post');

        $act = $this->actingAsAdminApi();
        $response = $act->post($url, $params);

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


    private function putRequest($params, $expectedMessage)
    {
        $url = route('acl.role.put', 1);

        $act = $this->actingAsAdminApi();
        $response = $act->put($url, $params);

        $response->assertStatus(400)->assertJson([
            'message' => $expectedMessage
        ]);
    }


    private function getValidationTestData()
    {
        return [
            [
                'params' => [
                    'name' => '',
                    'guard_name' => 'api',
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
            [
                'params' => [
                    'name' => 'aaa',
                    'guard_name' => 'bbb',
                    'permissions' => 'aaa'
                ],
                'message' => 'The permissions must be an array.'
            ],
            [
                'params' => [
                    'name' => 'aaa',
                    'guard_name' => 'bbb',
                    'permissions' => ['aaa']
                ],
                'message' => 'The permissions.0 must be an integer.'
            ]
        ];
    }
}
