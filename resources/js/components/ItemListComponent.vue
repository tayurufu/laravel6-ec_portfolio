<template>
    <div>
        <div class="container mb-md-3">

            <div>
               <button class="btn btn-info" v-if="canEdit" v-on:click="moveEditItemPage('')">新規登録</button>
            </div>

            <form class=" " @submit.prevent>
                <div class="input-group">

                    <select class="input-group-prepend" v-model="searchItemType">
                        <option value="">すべてのカテゴリー</option>
                        <option v-for="itemType in itemTypes" v-bind:value="itemType.id" v-bind:key="itemType.id" >{{ itemType.name }}</option>
                    </select>
                    <input type="text" name="searchName" v-model="searchName" class="form-control">
                    <div class="input-group-append">
                        <button type="button" class="btn btn-dark" v-on:click="searchClick"><i class="fas fa-search"></i></button>
                    </div>
                </div>
            </form>
        </div>

        <div class="container pt-3" id="item-content" ref="itemContent">

            <div class="item-line row mb-3 " v-for="item in items" :key="item.id" >

                <div class="item-box col-md-2">
                    <a :href="item.detailUrl" v-on:click.prevent.stop="moveDetailItemPage(item.detaiItemlUrl)">
                        <img :src="item.thumbnail" loading="lazy" alt=""  class="img-thumbnail h-auto" v-if="item.thumbnail"/>
                        <img src="/no_image.png" loading="lazy" alt="" class="img-thumbnail h-auto" v-else />
                    </a>
                </div>
                <div class="item-box col-md-10">
                    <div class="row mh-80">
                        <div class="col-md-2">
                            <div>
                                <a href="" v-on:click.prevent.stop="moveDetailItemPage(item.detailItemUrl)" >{{ item.name }}</a>
                            </div>
                            <div >
                                <a href="" v-on:click.prevent.stop="moveEditItemPage(item.editItemUrl)" v-if="canEdit">Edit</a>
                            </div>
                        </div>
                        <div class="col-md-10">
                            <div class="item-box col-md-12">

                            </div>
                            <div class="item-box col-md-12 mt-1">
                                金額: {{ item.price }}円
                            </div>
                            <div class="item-box col-md-12 mt-1">
                                カテゴリ: {{ item.itemType }}
                            </div>
                            <div class="item-box col-md-12 mt-1 description-container">
                                <div>詳細</div>
                                <div class="description-content">{{ item.description }}</div>
                            </div>
                            <div class="item-box col-md-12 mt-1 ">
                                タグ:
                                <span v-for="tag in item.tags"><i class="fas fa-tag ml-2"></i>{{ tag.tagName }}</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <infinite-loading ref="infiniteLoading" spinner="circle" @infinite="infiniteLoad">
                <span slot="no-more"><a href="" v-on:click.prevent.stop="scrollTop" class="w-100 d-block">↑</a></span>
                <span slot="no-results">-----データが存在しません-----</span>
            </infinite-loading>


            <div class="">

            </div>

        </div>
    </div>
</template>

<script>
    import InfiniteLoading from 'vue-infinite-loading';
    export default {
        props: {
            initData: {
                type: Object
            }
        },
        data() {
            return {
                items: [],
                itemTypes: [],
                canEdit: false,
                searchName: "",
                searchItemType: "",
                currentPage: 1,
            }
        },
        created() {
        },
        mounted() {
            this.itemTypes = this.initData.itemTypes
            this.canEdit = this.initData.canEdit
        },
        computed: {
        },
        methods: {
            moveEditItemPage(url){
                if(url){
                    location.href = url
                } else {
                    location.href = this.initData.createItemUrl
                }
            },
            moveDetailItemPage(url){
                location.href = url
            },
            searchClick(){
                this.items = [];
                this.currentPage = 1;
                this.$refs.infiniteLoading.stateChanger.reset();
            },
            searchItem(reset){
                let url = this.initData.getItemsUrl
                    + '?page=' + this.currentPage
                    + '&searchItemType=' + this.searchItemType
                    + '&searchName=' + this.searchName;
                let self = this


                axios.get(url).then( response => {
                    let items = response.data.items
                    self.items = [...self.items, ...items]
                    if(items.length === 0){
                        self.$refs.infiniteLoading.stateChanger.complete();
                    } else {
                        self.$refs.infiniteLoading.stateChanger.loaded();
                    }

                    self.currentPage++

                }).catch(self.errorHandler).finally(() => {

                });
            },
            infiniteLoad(){
                this.searchItem()
            },
            errorHandler(error){
                this.$refs.infiniteLoading.stateChanger.complete();

                if(error.response.data.message){
                    alert(error.response.data.message)
                }else {
                    console.error(error)
                    console.error(error.response)
                    alert('エラーが発生しました。')
                }
            },
            scrollTop() {
                console.log("go")
                this.$refs.itemContent.scrollTo({
                    top: 0,
                    behavior: "smooth"
                })
            }
        }
    }
</script>

<style scoped>

    #item-content{
        height: 70vh;
        overflow-y: auto;
    }
    .description-container{
        width: 100%;
    }
    .description-content{
        display: -webkit-box;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
        overflow-y: hidden;
        padding-left: 2rem;
    }
</style>
