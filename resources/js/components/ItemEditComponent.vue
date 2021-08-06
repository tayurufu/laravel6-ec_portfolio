<template>
    <div class="p-3">
        <form @submit.prevent  method="post" enctype="multipart/form-data" class="">
            <input type="hidden" name="item_id" v-model="item.id" >

            <h2 class="my-3">Item Edit</h2>
            <div class="form-group row">
                <label class="col-md-3 col-form-label">item id: </label>
                <input class="form-control col-md-9" type="text" id="item-id" name="item_id" maxlength="20" v-model="item.itemId" v-bind:readonly="isUpdate()">
            </div>
            <div class="form-group row">
                <label class="col-md-3 col-form-label">name: </label>
                <input class="form-control col-md-9" type="text" id="item-name" name="item_name" v-model="item.name">
            </div>
            <div class="form-group row">
                <label class="col-md-3 col-form-label">type: </label>
                <select class="item-type form-control col-md-9" name="item_type" v-model="item.itemType">
                    <option value="">choose Item Type</option>
                    <option v-for="itemType in itemTypes" v-bind:value="itemType.id" v-bind:key="itemType.id" >{{ itemType.name }}</option>
                </select>
            </div>
            <div class="form-group row">
                <label class="col-md-3 col-form-label">price: </label>
                <input class="form-control col-md-9" type="text" id="item-price" name="item_price" v-model="item.price" >
            </div>
            <div class="form-group row">
                <label class="col-md-3 col-form-label">qty: </label>
                <input class="form-control col-md-9" type="text" id="item-qty" name="item_qty" v-model="item.qty" >
            </div>
            <div class="form-group row">
                <label class="col-md-3 col-form-label">location: </label>
                <select class="item-type form-control col-md-9" name="item_location" v-model="item.location">
                    <option value="">choose Stock Location</option>
                    <option v-for="location in stockLocations" v-bind:value="location.id" v-bind:key="location.id" >{{ location.name }}</option>
                </select>
            </div>
            <div class="form-group row">
                <label class="col-md-3 col-form-label">description: </label>
                <textarea class="form-control col-md-9" id="item-description" name="item_description" v-model="item.description" ></textarea>
            </div>
            <div class="form-group row">
                <div class="form-inline" v-for="tag in tags">
                    <input class="tags form-control mr-3" name="tags[]" type="checkbox" v-bind:value="tag.id" v-model="selectedItemTag" v-bind:id="'tag_' + tag.id">
                    <label class="col-form-label mr-2" :for="'tag_' + tag.id"> {{ tag.name }} </label>
                </div>
            </div>

            <div>
                <div class="alert alert-danger alert-dismissible fade show" v-for="error in errors">
                    <button type="button" class="close" data-dismiss="alert">&times</button>
                    <span style="color: red">{{ error }}</span>
                </div>
            </div>
            <div class="row border border-primary p-2">
                <div class="col-md-3" v-for="n of 4">
                    <div class="row">
                        <div class="h-10 col-12 mb-2">
                            <img v-bind:src="getPhotoUrl(n-1)" class="img-fluid">
                        </div>
                        <button type="button" v-on:click="showFileUploadWindow(n-1)"  >add</button>
                        <input type="file" class="form-control-file col-12" name="photosDummy[]" :id="'add_' + (n-1)" @change="selectedFile($event, n - 1)" style="display:none;">
                        <button type="button" v-on:click="removeImage(n-1)">remove</button>
                    </div>
                </div>
            </div>

            <button class="btn btn-primary my-3" type="button" v-on:click="submitItem()">submit</button>
            <button class="btn btn-warning" type="button" v-on:click="deleteItem()" v-bind:disabled="!isUpdate()">削除</button>
            <button class="btn btn-info" type="button" v-on:click="backPage()">back</button>
        </form>
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
                item: {
                    id: "",
                    itemId: "",
                    name: "",
                    price: "",
                    itemType: "",
                    description: "",
                    qty: 0,
                    location: "",
                },
                errors: [],
                tags: [],
                itemTypes: [],
                stockLocations: [],
                selectedItemTag: [],
                photos: [],
                uploadPhotosMaxCount: 4,
                uploadPhotoData: [],
                mode: "create"
            }
        },
        mounted() {
            const itemData = this.initData.item;

            if(this.initData.mode === "update"){
                this.item = {
                    id: itemData.id,
                    itemId: itemData.itemId,
                    name: itemData.name,
                    price: itemData.price,
                    itemType: '' + itemData.typeId,
                    description: itemData.description,
                    qty: itemData.stock.qty ?? 0,
                    location: itemData.stock.location
                }

                this.selectedItemTag = this.initData.item.tags.map(($d) => {
                    return $d.tagId;
                })
            }

            this.tags = this.initData.tags;
            this.itemTypes = this.initData.itemTypes;
            this.stockLocations = this.initData.stockLocations;

            let self = this;
            this.uploadPhotoData = [...Array(self.uploadPhotosMaxCount)].map((d, i) => {
               return {
                   id: itemData?.photos[i]?.photoId ?? "",
                   url: itemData?.photos[i]?.url ?? "",
                   status: (itemData?.photos[i]) ? 2 : 0,
                   file: null
               }
            });

            this.mode = this.initData.mode

        },
        computed: {

        },
        methods: {
            backPage(){
                location.href = this.initData.backPageUrl
            },
            showFileUploadWindow(i){
                let refname = "add_" + i
                document.getElementById(refname).click()
            },
            selectedFile: function(e, i) {

                e.preventDefault();
                let files = e.target.files;

                if(files[0] !== null && files[0] !== undefined){

                    this.createImage(files[0], i)

                    this.uploadPhotoData[i].file = files[0];
                    if(this.mode === "update" && this.uploadPhotoData[i].id !== ""){
                        this.uploadPhotoData[i].status = 3
                    } else {
                        this.uploadPhotoData[i].status = 1
                    }
                } else {
                    //this.uploadPhotoData[i].file = null;
                    //this.uploadPhotoData[i].url = ""
                }

            },
            createImage(file, i) {
                const reader = new FileReader();
                reader.onload = e => {
                    this.uploadPhotoData[i].url = e.target.result;
                };
                reader.readAsDataURL(file);
            },
            removeImage(i) {
                this.uploadPhotoData[i].file = null;
                this.uploadPhotoData[i].url = "";

                if(this.mode === "create"){
                    this.uploadPhotoData[i].status = 0;
                } else if(this.mode === "update"
                    && this.uploadPhotoData[i].id !== ""){
                    this.uploadPhotoData[i].status = 4;
                } else {
                    this.uploadPhotoData[i].status = 0;
                }

            },
            submitItem(){
                const params = new FormData();
                params.append('id', this.item.id);
                params.append('item_id', this.item.itemId);
                params.append('item_name', this.item.name);
                params.append('item_type', this.item.itemType);
                params.append('item_price', this.item.price);
                params.append('item_description', this.item.description);
                params.append('item_qty', this.item.qty ?? 0);
                params.append('item_location', this.item.location);

                this.selectedItemTag.forEach((data) => {
                    params.append('tags[]', data);
                });

                this.uploadPhotoData.forEach((data) => {

                    params.append('photoStatus[]', data.status);
                    params.append('photoIds[]', data.id);

                    //dummy
                    var content = '<a id="a"><b id="b">hey!</b></a>';
                    var blob = new Blob([content], { type: "text/xml"});

                    params.append('photos[]', data.file ?? blob );

                });

                axios.post(this.initData.storeUrl, params).then( (response => {
                    alert('ok')
                    location.href = response.data.redirectUrl

                })).catch( (error) => {
                    alert(error.response.data.message)
                    console.log(error.response)
                });
            },
            deleteItem(){
                axios.delete(this.initData.deleteUrl, {'id': this.item.id}).then(response => {
                    alert('ok')
                    location.href = this.initData.backPageUrl
                }).catch( (error) => {
                    alert(error.response.data)
                    console.log(error.response)
                });
            },
            getPhotoUrl(i){
                let rtn =  this.uploadPhotoData[i]?.url ?? this.initData.dummyImage
                if(rtn === ""){
                    rtn = this.initData.dummyImage
                }
                return rtn

            },
            isUpdate(){
                return this.mode === "update"
            }
        },

    }
</script>

<style scoped>

</style>
