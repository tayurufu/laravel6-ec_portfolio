<template>
    <div>
        <div class="container">
            <h2>{{ item.name }}</h2>
        </div>

        <div class="container py-3">
            <div class="row">
                <div id="item_img" class="mx-auto col-md-5 carousel slide" data-ride="carousel">
                    <ol class="carousel-indicators">
                         <li data-target="#item_img" :data-slide-to="index" :class="{ 'active': index === 0 }" v-for="(photo, index) in item.photos"></li>
                    </ol>
                    <div class="carousel-inner">
                        <div class="carousel-item" :class="{ 'active': index === 0 }" v-for="(photo, index) in item.photos" >
                            <img class="img-fluid" :src="photo.url" alt="item">
                        </div>
                    </div>
                    <a class="carousel-control-prev" href="#item_img" role="button" data-slide="prev">
                        <span class="carousel-control-prev-icon"></span>
                    </a>
                    <a class="carousel-control-next" href="#item_img" role="button" data-slide="next">
                        <span class="carousel-control-next-icon"></span>
                    </a>
                </div>
            </div>
        </div>

        <div class="container py-5">
            <div class="row">
                <div class="col-md-9">
                    <div class="row py-1">
                        <div class="col-md-12">
                            金額: {{ item.price }}円
                        </div>
                    </div>
                    <div class="row py-1">
                        <div class="col-md-12">
                            カテゴリ: {{ item.itemType }}
                        </div>
                    </div>
                    <div class="row py-1">
                        <div class="col-md-12">
                            タグ:
                            <div class="d-inline-block ml-3" v-for="tag in item.tags">
                                <i class="fas fa-tag"></i><span>{{ tag.tagName }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="row py-1">
                        <div class="col-md-12">
                            <div>詳細</div>
                            <div class="description-content">{{ item.description }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div>
                        <div v-if="!hasCart">
                            <input type="number" v-model="qty" min="1" :max="maxQty" step="1" />
                            <button type="button" v-on:click="addCart()" class="btn btn-warning">カートに追加</button>
                        </div>
                        <button type="button" v-on:click="removeCart()" class="btn btn-danger" v-else>カートから削除</button>
                        <div v-if="!isLogin" style="color:red">カートの中身を見るにはログインしてください。</div>
                    </div>
                </div>
            </div>

        </div>

        <div class="container">
            <button class="btn btn-primary" type="button" v-on:click="goBackPage">戻る</button>
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
                item: {
                    display_name: "",
                    tags: [],
                    price : "",
                    description: "",
                    itemType: ""
                },

                qty: 1,
                maxQty: 10,
                hasCart: false,
                isLogin: false,
            }
        },
        created() {
        },
        mounted() {
            this.item = this.initData.item
            this.hasCart = this.initData.hasCart
            this.isLogin = this.initData.isLogin
        },
        computed: {
        },
        methods: {
            goBackPage(){
                location.href = this.initData.backPageUrl
            },
            addCart(){
                let self = this
                let params = {'qty': this.qty}
                axios.post(this.initData.addCartUrl, params).then(
                    response => {
                        self.hasCart = true
                    }
                ).catch(self.errorHandler);
            },
            removeCart(){
                let self = this
                axios.post(this.initData.removeCartUrl).then(
                    response => {
                        self.hasCart = false
                    }
                ).catch(self.errorHandler);
            },
            hasPhotos(){
                 if(!this.item.photos){
                    return false
                }

                return this.item.photos.length === 0
            },
            errorHandler(error){

                if(error.response.data.message){
                    alert(error.response.data.message)
                }else {
                    console.error(error)
                    console.error(error.response)
                    alert('エラーが発生しました。')
                }
            },
        }
    }
</script>

<style scoped>
    .description-content{
        padding-left: 2rem;
    }
</style>
