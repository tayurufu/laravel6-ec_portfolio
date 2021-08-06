<template>

    <div class="container">
        <h3>カート内容</h3>
        <p class="mt-3">購入・削除する商品を選択してください。</p>
        <div class="mt-3">
            <button class="btn btn-primary" v-on:click="backPage()">戻る</button>
            <button class="btn btn-danger" v-on:click="removeCartItems()" >カートから削除</button>
            <button class="btn btn-success" v-on:click="buyCartItems()" >購入</button>
            <button class="btn btn-info" v-on:click="selectAllItems()" >全選択・解除</button>
        </div>
        <div class="row justify-content-center mt-5" v-for="item in items" :key="item.id">
            <div class="item-line row mb-3 ">
                <div class="col-md-1">
                    <input type="checkbox" v-model="checkedItem" :value="item" :disabled="!checkItemEnabled(item)">
                </div>
                <div class="item-box col-md-2">
                    <a v-bind:href="item.detailUrl">
                        <img v-bind:src="item.thumbnail" loading="lazy" alt=""  class="img-thumbnail h-auto" v-if="hasUrl(item.thumbnail)" />

                        <i class="img-thumbnail fas fa-image fa-5x" v-else></i>
                    </a>
                </div>
                <div class="col-md-9 row">
                    <div class="item-box col-md-12">
                        商品名: {{ item.name }}
                    </div>
                    <div class="item-box col-md-12">
                        点数: {{ item.qty }}
                    </div>
                    <div class="item-box col-md-12">
                        金額: {{ item.price }} × {{ item.qty }} = {{ item.price * item.qty }} 円
                    </div>
                    <div class="item-box col-md-12">
                        <p v-if="checkItemEnabled(item)">在庫あり</p>
                        <p v-else style="color: red">在庫切れ</p>
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
            },
        },
        data() {
            return {
                checkedItem: [],
                items: [],
                isAllSelected: false,
            }
        },
        mounted() {
            this.items = this.getCartItems();
        },
        computed: {

        },
        methods: {
            async getCartItems(){
                let self = this
                axios.get(this.initData.getCartItemsUrl).then(response => {
                    const resdata = response.data
                    //console.log(resdata)
                    self.items = resdata.myCartItems
                }).catch(self.errorHandler);
            },
            hasUrl(value){
                return (value !== null) && (value !== undefined )
            },
            backPage(){
                location.href = this.initData.backUrl
            },
            removeCartItems(){
                if(this.checkedItem.length === 0){
                    alert('カートから削除する商品を選択してください。')
                    return false
                }

                const removeItems = this.checkedItem.map(function($d){
                   return $d.id
                });

                let self = this
                axios.post(this.initData.removeCartItemsUrl, {'removeCartItems': removeItems})
                .then(response => {
                    //console.log(response.data)
                    self.getCartItems()
                    self.checkedItem = []

                }).catch(self.errorHandler);

            },
            async buyCartItems(){

                let registeredCustomer = await this.checkRegisterdCustomer()
                if(!registeredCustomer){
                    location.href =  this.initData.editCustomerUrl + "?redirectUrl=" + location.href
                    return
                }

                if(this.checkedItem.length === 0){
                    alert('購入する商品を選択してください。')
                    return false
                }

                if(!confirm('選択した商品を購入します。よろしいですか？')){
                   return
                }

                const buyItems = this.checkedItem.map(function($d){
                    return { 'id' : $d.id, 'qty': $d.qty}
                });

                let self = this
                axios.post(this.initData.buyCartItemsUrl, {'buyCartItems': buyItems})
                    .then(response => {
                        alert('購入が完了しました。')
                        self.getCartItems()
                        self.checkedItem = []
                    }).catch(self.errorHandler);

            },

            async checkRegisterdCustomer(){

                try{
                    let response = await axios.get(this.initData.getCustomerUrl)
                    let tmpCustomer = response.data
                    return !!(tmpCustomer && tmpCustomer.id);
                }catch(error){
                    this.errorHandler(error)
                    throw error
                }

            },
            checkItemEnabled(item){

                return !!item.hasStock
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

            selectAllItems(){
                this.isAllSelected = !this.isAllSelected;

                let newCheckedItems = [];

                if(this.isAllSelected){
                    this.items.filter(this.checkItemEnabled).forEach(function(item){
                        newCheckedItems.push(item);
                    });
                }

                this.checkedItem = newCheckedItems;
            }
        }
    }
</script>

<style scoped>

</style>
