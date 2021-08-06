<?php

namespace Tests\Feature\Acl;

use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class AclUserTest extends TestCase
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
     * user一覧取得リクエスト(正常系)
     *
     * @test
     * @return void
     */
    public function usersGetOk()
    {
        $url = route('acl.users.get');

        $act = $this->actingAsAdminApi();
        $response = $act->get($url);

        $expectedCount = User::count();

        $response->assertOk();

        $content = json_decode($response->content(), true);
        $this->assertEquals($expectedCount, count($content['users']));

    }

    /**
     * userの更新(正常系)
     *
     * @test
     */
    public function userAclUpdateOk(){

        $createdData = User::create([
            'name' => 'test_user',
            'email' => 'test@example.com'
        ]);

        $url = route('acl.user.grant', $createdData->id);

        $params = [
            'roles' => [1,3],
            'permissions' => [2]
        ];

        $act = $this->actingAsAdminApi();
        $response = $act->put($url, $params);

        $response->assertOk()
            ->assertJson([
                'user' => [
                    'id' => $createdData->id,
                    'roles' => [1,3],
                    'permissions' => [2]
                ]
            ]);

    }

    /**
     * Permissionの更新 バリデーションエラー(異常系)
     *
     * @ test
     */
    public function userUpdateValidationNg(){

        $data = $this->getValidationTestData();
        foreach($data as $d)
        {
            $this->putValidationRequest($d['params'], $d['message']);
        }

    }

    /**
     * Permissionの更新 存在しないパーミッション(異常系)
     *
     * @test
     */
    public function permissionUpdateNotFoundNg(){

        $createdData = User::create([
            'name' => 'test_user',
            'email' => 'test@example.com'
        ]);

        $url = route('acl.user.grant', $createdData->id);

        $createdData->delete();

        $params = [
            'roles' => [1,3],
            'permissions' => [2]
        ];

        $act = $this->actingAsAdminApi();
        $response = $act->put($url, $params);

        $response->assertStatus(400)
            ->assertJson([
                'message' => '存在しないユーザーです。'
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

        $getAllUrl = route('acl.users.get');
        $createUrl = route('acl.user.grant', 1);

        $response = $this->get($getAllUrl);
        $response->assertStatus($expectedStatus);

        $response = $this->put($createUrl);
        $response->assertStatus($expectedStatus);


        $expectedStatus = 403;

        $user = User::where(['email' => 'manager@gmail.com'])->first();
        $act = $this->actingAs($user);

        $response = $act->get($getAllUrl);
        $response->assertStatus($expectedStatus);

        $response = $act->put($createUrl);
        $response->assertStatus($expectedStatus);

    }

    public function getValidationTestData()
    {
        return
            [
                [
                    'params' => [
                        'roles' => ["a"],
                        'permissions' => [],
                    ],
                    'message' => 'The roles.0 must be an integer.'
                ],
                [
                    'params' => [
                        'roles' => [],
                        'permissions' => ["a"],
                    ],
                    'message' => 'The permissions.0 must be an integer.'
                ],
                [
                    'params' => [
                        'roles' => "a",
                        'permissions' => [],
                    ],
                    'message' => 'The roles must be an array.'
                ],
                [
                    'params' => [
                        'roles' => [],
                        'permissions' => "a",
                    ],
                    'message' => 'The permissions must be an array.'
                ],
            ];
    }

    public function putValidationRequest($params, $expectedMessage)
    {
        $url = route('acl.user.grant', 1);

        $act = $this->actingAsAdminApi();
        $response = $act->put($url, $params);

        $response->assertStatus(400)->assertJson([
            'message' => $expectedMessage
        ]);
    }
}
