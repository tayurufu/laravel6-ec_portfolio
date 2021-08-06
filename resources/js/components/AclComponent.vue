<template>
    <div class="container">
        <div class="nav nav-tabs" id="tab-menus">
            <a class="nav-item nav-link active" id="tab-menu01" data-toggle="tab" href="#user-panel">USER</a>
            <a class="nav-item nav-link" id="tab-menu02" data-toggle="tab" href="#role-panel">ROLE</a>
            <a class="nav-item nav-link" id="tab-menu03" data-toggle="tab" href="#permission-panel">PERMISSION</a>
        </div>
        <div class="tab-content" id="pannel-menus">
            <div class=" tab-pane fade show active border border-top-0" id="user-panel">
                <div class="p-2">
                    <h3>USERS</h3>
                    <div class="col-md-12">

                    </div>
                    <ul class="pt-2">
                        <li v-for="user in users" :key="user.id">
                            <a href="" v-on:click.prevent="openUser(user.id)" data-toggle="modal" data-target="#user-content">{{ user.name }}</a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class=" tab-pane fade border border-top-0"  id="role-panel">
                <div class="p-2">
                    <h3>ROLES</h3>
                    <div class="col-md-12">
                        <button class="btn btn-primary" type="button" v-on:click="openRole('')" data-toggle="modal" data-target="#role-content">Create New Role</button>
                    </div>
                    <ul class="pt-2">
                        <li v-for="role in roles" :key="role.id">
                            <a href="" v-on:click.prevent="openRole(role.id)" data-toggle="modal" data-target="#role-content">{{ role.name }}</a>
                            {{ role.guard_name}}
                        </li>
                    </ul>
                </div>
            </div>
            <div class=" tab-pane fade border border-top-0" id="permission-panel">
                <div class="p-2">
                    <h3>PERMISSIONS</h3>
                    <div class="col-md-12">
                        <button class="btn btn-primary" type="button" v-on:click="openPermission('') " data-toggle="modal" data-target="#permission-content">Create New Permission</button>
                    </div>
                    <ul class="pt-2">
                        <li v-for="permission in permissions" :key="permission.id">
                            <a href="" v-on:click.prevent="openPermission(permission.id)" data-toggle="modal" data-target="#permission-content">{{ permission.name }}</a>
                            {{ permission.guard_name}}
                        </li>
                    </ul>
                </div>
            </div>
        </div>




        <div class=" modal fade"  :class='{ "hiddenEditContent": !isEditUser }' id="user-content">
            <div class="modal-dialog modal-dialog-centered modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3>USER: {{ targetUser.name }}</h3>
                        <button type="button" class="close" data-dismiss="modal" v-on:click="closeUser" ref="closeUserContent">
                            <span>&times</span>
                        </button>
                    </div>
                    <div class="modal-body row ">
                        <input type="hidden" name="id" :value="targetUser.id">
                        <div class="col-md-6" style="height: 90%">
                            <p>ROLES</p>
                            <div class="scroll-wrapper" >
                                <div class="form-inline" v-for="role in roles" :key="role.id">
                                    <input class="roles form-control mr-3" name="user-roles[]" type="checkbox" v-bind:value="role.id" v-model="targetUser.roles">
                                    <label class="col-form-label mr-2">{{ role.name }} / {{ role.guard_name }}</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6" style="height: 90%">
                            <p>PERMISSIONS</p>
                            <div class="scroll-wrapper" >
                                <div class="form-inline" v-for="permission in permissions" >
                                    <input class="permissions form-control mr-3" name="user-permissions[]" type="checkbox" v-bind:value="permission.id" v-model="targetUser.permissions">
                                    <label class="col-form-label mr-2">{{ permission.name }} / {{ permission.guard_name }}</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" v-on:click="saveUser(targetUser)">save</button>
                        <button type="button" v-on:click="closeUser" data-dismiss="modal">close</button>
                    </div>
                </div>
            </div>
        </div>

        <div class=" modal fade"  :class='{ "hiddenEditContent": !isEditRole }' id="role-content" tabindex="-1" role="dialog" aria-labelledby="myExtraLargeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3>ROLE: {{ targetRole.name }}</h3>
                        <button type="button" class="close" data-dismiss="modal" v-on:click="closeRole" ref="closeRoleContent">
                            <span>&times</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="text" name="roleName" v-model="targetRole.name">
                        <input type="text" name="roleGuard" v-model="targetRole.guard">
                        <input type="hidden" name="id" :value=" targetRole.id ">

                        <p style="margin-top:10px;">ROLES</p>
                        <div class="scroll-wrapper"  style="height: 90%; padding-bottom:2rem;">
                            <div class="form-inline" v-for="permission in permissions" >
                                <input class="permissions form-control mr-3" name="role-permissions[]" type="checkbox" v-bind:value="permission.id" v-model="targetRole.permissions">
                                <label class="col-form-label mr-2">{{ permission.name }} / {{ permission.guard_name }}</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" v-on:click="saveRole(targetRole)">save</button>
                        <button type="button" :disabled="mode===0" v-on:click="deleteRole(targetRole)">remove</button>
                        <button type="button" v-on:click="closeRole" data-dismiss="modal">close</button>
                    </div>
                </div>
            </div>
        </div>

        <div class=" modal fade" :class='{ "hiddenEditContent": !isEditPermission }' id="permission-content" tabindex="-1" role="dialog" aria-labelledby="myExtraLargeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3>PERMISSION: {{ targetPermission.name }}</h3>
                        <button type="button" class="close" data-dismiss="modal" v-on:click="closePermission" ref="closePermissionContent">
                            <span>&times</span>
                        </button>
                    </div>
                    <div class="modal-body">

                        <input type="text" name="permissionName" v-model="targetPermission.name">
                        <input type="text" name="permissionGuard" v-model="targetPermission.guard">

                        <input type="hidden" name="id" :value=" targetPermission.id ">
                    </div>
                    <div class="modal-footer">
                        <button type="button"  v-on:click="savePermission(targetPermission)">save</button>
                        <button type="button" :disabled="mode===0" v-on:click="deletePermission(targetPermission)">remove</button>
                        <button type="button" v-on:click="closePermission" data-dismiss="modal">close</button>
                    </div>
                </div>
            </div>
        </div>


    </div>
</template>

<script>
    export default {
        props: {
            initData: {
                type: Object
            }
        },
        data() {
            return {
                roles: [],
                users: [],
                permissions: [],
                isEditUser: false,
                isEditRole: false,
                isEditPermission: false,
                targetPermission: {
                    id: "",
                    name: "",
                    guard: "",
                    url: "",
                },
                targetUser: {
                    id: "",
                    name: "",
                    roles: [],
                    permissions: [],
                    url: "",
                },
                targetRole: {
                    id: "",
                    name: "",
                    guard: "",
                    permissions: [],
                    url: "",
                },
                mode: 0
            }
        },
        mounted() {
            this.getAclUsers();
            this.getRoles();
            this.getPermissions();
            this.initTargetUser();
            this.initTargetRole();
            this.initTargetPermission();
        },
        computed: {

        },
        methods: {
            getAclUsers(){
                let self = this
                axios.get(this.initData.getUsersUrl)
                    .then(response => {
                        self.users = response.data.users
                    })
            },
            getRoles(){
                let self = this
                axios.get(this.initData.getRolesUrl)
                    .then(response => {
                        self.roles = response.data.roles
                    })
            },
            getPermissions(){
                let self = this

                axios.get(this.initData.getPermissionsUrl)
                    .then(response => {
                        self.permissions = response.data.permissions
                    })
            },
            goBackPage(){
                //location.href = this.initData.backPageUrl
            },


            openUser(targetId){
                this.initTargetUser()

                if(targetId === "" || targetId === undefined || targetId === null){
                    alert('error')
                    return
                }

                const target = this.users.find( data => data.id === targetId)

                if(target === undefined){
                    return false
                }

                this.isEditUser = true
                this.targetUser = {
                    id: target.id,
                    name: target.name,
                    roles: target.roles,
                    permissions: target.permissions,
                    url: target.url,
                }

                this.isEditUser = true
                this.mode = 2
            },
            saveUser(target){

                const param = {
                    id: target.id,
                    name: target.name,
                    roles: target.roles,
                    permissions: target.permissions,
                }

                if(this.mode === 0){
                    alert('error')
                } else {
                    let self = this
                    axios.put(target.url, param).then(
                        response => {
                            self.getAclUsers()
                            alert('ok')
                            self.$refs.closeUserContent.click()
                        }
                    ).catch(self.errorHandler);
                }


            },
            closeUser(){
                this.isEditUser = false
                this.initTargetUser()
            },
            initTargetUser(){
                this.targetUser = {
                    id: "",
                    name: "",
                    roles: [],
                    permissions: [],
                    url: "",
                }
            },


            openRole(targetId){
                this.initTargetRole()

                if(targetId === "" || targetId === undefined || targetId === null){
                    this.mode = 0
                    this.isEditRole = true
                    return
                }

                const target = this.roles.find( data => data.id === targetId)

                if(target === undefined){
                    return false
                }

                this.isEditRole = true
                this.targetRole = {
                    id: target.id,
                    name: target.name,
                    guard: target.guard_name,
                    permissions: target.permissions,
                    url: target.url,
                }

                this.isEditRole = true
                this.mode = 2
            },
            saveRole(target){

                const param = {
                    id: target.id,
                    name: target.name,
                    guard_name: target.guard,
                    permissions: target.permissions
                }

                let self = this

                if(this.mode === 0){

                    axios.post(this.initData.createRoleUrl, param).then(
                        response => {
                            self.getRoles()
                            alert('ok')
                            self.$refs.closeRoleContent.click()
                        }
                    ).catch( self.errorHandler);
                } else {
                    axios.put(target.url, param).then(
                        response => {
                            self.getRoles()
                            alert('ok')
                            self.$refs.closeRoleContent.click()
                        }
                    ).catch(self.errorHandler);
                }


            },
            deleteRole(target){
                let self = this
                axios.delete(target.url).then(
                    response => {
                        self.getRoles()
                        alert('ok')
                        self.$refs.closeRoleContent.click()
                    }
                ).catch( self.errorHandler);
            },
            closeRole(){
                this.isEditRole = false
                this.initTargetRole()
            },
            initTargetRole(){
                this.targetRole = {
                    id: "",
                    name: "",
                    guard: "",
                    permissions: [],
                    url: "",
                }
            },

            openPermission(targetId){

                this.initTargetPermission()

                if(targetId === "" || targetId === undefined || targetId === null){
                    this.mode = 0
                    this.isEditPermission = true
                    return
                }

                const target = this.permissions.find( data => data.id === targetId)

                if(target === undefined){
                    return false
                }

                this.isEditPermission = true
                this.targetPermission = {
                    id: target.id,
                    name: target.name,
                    guard: target.guard_name,
                    url: target.url,
                }

                this.isEditPermission = true
                this.mode = 2
            },
            savePermission(target){

                const param = {
                    id: target.id,
                    name: target.name,
                    guard_name: target.guard,
                }

                if(this.mode === 0){
                    let self = this
                    axios.post(this.initData.createPermissionUrl, param).then(
                        response => {
                            self.getPermissions()
                            alert('ok')
                            self.$refs.closePermissionContent.click()
                        }
                    ).catch( self.errorHandler);
                } else {
                    let self = this
                    axios.put(target.url, param).then(
                      response => {
                          self.getPermissions()
                          alert('ok')
                          self.$refs.closePermissionContent.click()
                      }
                    ).catch(self.errorHandler);
                }


            },
            deletePermission(target){
                let self = this
                axios.delete(target.url).then(
                    response => {
                        self.getPermissions()
                        alert('ok')
                        self.$refs.closePermissionContent.click()
                    }
                ).catch( self.errorHandler);
            },
            closePermission(){
                this.isEditPermission = false
                this.initTargetPermission()
            },
            initTargetPermission(){
                this.targetPermission = {
                    id: "",
                    name: "",
                    guard: "",
                    url: "",
                }
            },
            errorHandler(error){

                if(error.response.data.message){
                    alert(error.response.data.message)
                }else {
                    console.error(error)
                    console.error(error.response)
                    alert('エラーが発生しました。')
                }
            }

        }
    }
</script>

<style scoped>
    .hiddenEditContent{
        /*display: none;*/
    }

    .modal-body{
        height: 60vh;

    }

    .scroll-wrapper{
        height: 100%;
        overflow-y: auto;
    }
</style>
