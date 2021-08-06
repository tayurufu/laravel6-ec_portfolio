<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Acl\PermissionRequest;
use App\Http\Requests\Acl\RoleRequest;
use Illuminate\Http\JsonResponse;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\User;


use Illuminate\Support\Facades\DB;

class AclController extends Controller
{
    /**
     * Acl編集画面遷移時
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(){

        $getUsersUrl = route('acl.users.get');
        $getRolesUrl = route('acl.roles.get');
        $getPermissionsUrl = route('acl.permissions.get');
        $createRoleUrl = route('acl.role.post');
        $createPermissionUrl = route('acl.permission.post');

        $data = [
            'getUsersUrl' => $getUsersUrl,
            'getRolesUrl' => $getRolesUrl,
            'getPermissionsUrl' => $getPermissionsUrl,
            'createRoleUrl' => $createRoleUrl,
            'createPermissionUrl' => $createPermissionUrl,
        ];

        return view('admin.acl.index', compact( 'data'));
    }

    /**
     * api User一覧取得
     * @return array
     */
    public function getUsers(){

        $users = User::select(['id', 'name', 'email'])->get()->map(function($d){

            return (object)[
                'id' => $d->id,
                'name' => $d->name,
                'email' => $d->email,
                'url' => route('acl.user.get', $d->id),
                'roles' => $d->roles()->select('id')->pluck('id')->toArray(),
                'permissions' => $d->permissions()->select('id')->pluck('id')->toArray()
            ];
        });

        return ['users' => $users];
    }

    public function getUser($id){

        $user = User::find($id);
        $grantedRoles = $user->roles()->select('id')->pluck('id')->toArray();
        $user->grantedRoles = $grantedRoles;

        if($user === null){
            return new JsonResponse(
                [
                    "message" => "存在しないユーザーです。"
                ],
                400 );
        }

        return ['user' => $user];
    }

    /**
     * api Role一覧取得
     * @return array
     */
    public function getRoles(){

        $roles = Role::select(['id', 'name', 'guard_name'])->get()->map(function($d){
            return (object)[
                'id' => $d->id,
                'name' => $d->name,
                'guard_name' => $d->guard_name,
                'url' => route('acl.role.get', $d->id),
                'permissions' => $d->permissions()->select('id')->pluck('id')->toArray()
            ];
        });

        return ['roles' => $roles] ;
    }

    public function getRole($id){

        $role = Role::find($id);

        if($role === null){
            return new JsonResponse(
                [
                    "message" => "存在しないロールです。"
                ],
                400 );
        }

        return ['role' => $role] ;
    }

    /**
     * api Permission一覧取得
     * @return array
     */
    public function getPermissions(){

        $permissions = Permission::select(['id', 'name', 'guard_name'])->get()->map(function($d){
            $d->url = route('acl.permission.get', $d->id);
            return $d;
        });

        return ['permissions' => $permissions];
    }

    public function getPermission($id){

        $permission = Permission::find($id);

        if($permission === null){
            return new JsonResponse(
                [
                    "message" => "存在しないパーミッションです。"
                ],
                400 );
        }

        return ['permission' => $permission];
    }

    /**
     * api UserにRoleとPermission付与
     * @param $userId
     * @return JsonResponse
     * @throws \Exception
     */
    public function grant($userId){

        $user = User::find($userId);
        if($user === null){
            return new JsonResponse(
                [
                    "result" => "ng",
                    "message" => "存在しないユーザーです。"
                ], 400);
        }

        $newRoles = \request()->get('roles') ?? [];
        $newPermissions = \request()->get('permissions') ?? [];

        DB::beginTransaction();
        try {

            $user->roles()->sync($newRoles);
            $user->permissions()->sync($newPermissions);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();

            throw $e;
        }

        $newRoles = $user->roles()->select(['id'])->pluck('id')->toArray();
        $newPermissions =  $user->permissions()->select(['id'])->pluck('id')->toArray();

        return new JsonResponse(
            [
                "result" => "ok",
                "user" =>  (object)[
                        'id' => $user->id,
                        'roles' => $newRoles,
                        'permissions' => $newPermissions
                    ]
            ]);
    }

    /**
     * ロールの登録・更新共通
     * @param $role
     * @param $roleName
     * @param $roleGuard
     * @param $permissions
     * @return object
     * @throws \Exception
     */
    private function storeRole($role, $roleName, $roleGuard, $permissions){

        $newRole = null;

         DB::beginTransaction();
        try {

            if($role === null){
                $newRole = Role::create(['name' => $roleName, 'guard_name' => $roleGuard]);
            } else {
                $newRole = $role;
                $newRole->name = $roleName;
                $newRole->guard_name = $roleGuard;
                $newRole->save();
            }

            $newRole->permissions()->sync($permissions);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();

            throw $e;
        }

        $newPermissions = $newRole->permissions()->select(['id'])->pluck('id')->toArray();

        return (object)[
                'name' => $newRole->name,
                'guard_name' => $newRole->guard_name,
                'permissions' => $newPermissions
            ];
    }


    /**
     * ロールの新規登録
     * @param RoleRequest $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function createRole(RoleRequest $request)
    {

        $roleName = trim(\request()->get('name') ?? "");
        $roleGuard = trim(\request()->get('guard_name') ?? "");
        $permissions = \request()->get('permissions') ?? [];

        $role = Role::where(['name' => $roleName, 'guard_name' => $roleGuard])->first();

        if($role !== null){
            return new JsonResponse(
                [
                    "result" => "ng",
                    "message" => "すでに存在するロールです。"
                ], 400);
        }

        $resultRole = $this->storeRole(null, $roleName, $roleGuard, $permissions);
        return new JsonResponse(
            [
                "result" => "ok",
                "role" => $resultRole
            ]);

    }

    /**
     * ロールの更新
     * @param RoleRequest $request
     * @param $roleId
     * @return JsonResponse
     * @throws \Exception
     */
    public function updateRole(RoleRequest $request, $roleId)
    {
        $roleName = trim(\request()->get('name') ?? "");
        $roleGuard = trim(\request()->get('guard_name') ?? "");
        $permissions = \request()->get('permissions') ?? [];

        $role = Role::find($roleId);
        if($role === null){
            return new JsonResponse(
                [
                    "result" => "ng",
                    "message" => "存在しないロールです。"
                ], 400);
        }

        $duplicatedRole = Role::where(['name' => $roleName, 'guard_name' => $roleGuard])->where('id','<>', $roleId)->first();

        if($duplicatedRole !== null){
            return new JsonResponse(
                [
                    "result" => "ng",
                    "message" => "他のロールと値が重複しています。"
                ], 400);
        }

        $resultRole = $this->storeRole($role, $roleName, $roleGuard, $permissions);
        return new JsonResponse(
            [
                "result" => "ok",
                "role" => $resultRole
            ]);
    }

    /**
     * ロールの削除
     * @param $roleId
     * @return JsonResponse
     * @throws \Exception
     */
    public function deleteRole($roleId)
    {
        $role = Role::find($roleId);
        if($role === null){
            return new JsonResponse(
                [
                    "result" => "ng",
                    "message" => "存在しないロールです。"
                ], 400);
        }

        DB::beginTransaction();
        try {
            $role->delete();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();

            throw $e;
        }

        return new JsonResponse(
            [
                "result" => "ok",
                "roleId" => $roleId
            ]);
    }


    /**
     * パーミッションの新規登録
     * @param PermissionRequest $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function createPermission(PermissionRequest $request){

        $permissionId = null;
        $permissionName = trim(\request()->get('name') ?? "");
        $permissionGuard = trim(\request()->get('guard_name') ?? "web");

        $permission = Permission::where(['name' => $permissionName, 'guard_name' => $permissionGuard])->first();

        if($permission !== null){
            return new JsonResponse(
                [
                    "result" => "ng",
                    "message" => "すでに存在するパーミッションです。"
                ], 400);
        }

        DB::beginTransaction();
        try {
            $createdPermission = Permission::create(['name' => $permissionName, 'guard_name' => $permissionGuard]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();

            throw $e;
        }

        return new JsonResponse(
            [
                "result" => "ok",
                "permission" => $createdPermission
            ]);
    }

    /**
     * パーミッションの更新
     * @param PermissionRequest $request
     * @param $permissionId
     * @return JsonResponse
     * @throws \Exception
     */
    public function updatePermission(PermissionRequest $request, $permissionId){

        $permissionName = trim(\request()->get('name') ?? "");
        $permissionGuard = trim(\request()->get('guard_name') ?? "web");

        $permission = Permission::find($permissionId);

        if($permission === null){
            return new JsonResponse(
                [
                    "result" => "ng",
                    "message" => "存在しないパーミッションです。"
                ], 400);
        }

        $duplicated = Permission::where(['name' => $permissionName, 'guard_name' => $permissionGuard])->where('id','<>', $permissionId)->first();
        if($duplicated !== null){
            return new JsonResponse(
                [
                    "result" => "ng",
                    "message" => "他のパーミッションと値が重複しています。"
                ], 400);
        }

        DB::beginTransaction();
        try {

            $permission->name = $permissionName;
            $permission->guard_name = $permissionGuard;
            $permission->save();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();

            throw $e;
        }

        return new JsonResponse(
            [
                "result" => "ok",
                "permission" => $permission
            ]);
    }

    /**
     * パーミッションの削除
     * @param $permissionId
     * @return JsonResponse
     * @throws \Exception
     */
    public function deletePermission($permissionId){

        $permission = Permission::find($permissionId);

        if($permission === null){
            return new JsonResponse(
                [
                    "result" => "ng",
                    "message" => "存在しないパーミッションです。"
                ], 400);
        }

        DB::beginTransaction();
        try {
            $permission->delete();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();

            throw $e;
        }

        return new JsonResponse(
            [
                "result" => "ok",
                "permissionId" => $permission->id
            ]);
    }

}
