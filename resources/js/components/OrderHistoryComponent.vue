<template>
    <div>

        <div class="container pt-3" id="order-content" ref="orderContent">

            <div class="order-line row mb-3 justify-content-center border-info" v-for="order in orders" :key="order.id" >
                <div class="order-box col-md-5 text-left">
                    <a href="" v-on:click.prevent="showDetail(order)" data-toggle="modal" data-target="#modal-content">
                    注文日時: {{ order.order_time}}
                    </a>
                </div>
                <div class="order-box col-md-3 text-left">
                    総額: {{ order.total_price }}円
                </div>

            </div>
            <infinite-loading ref="infiniteLoading" spinner="circle" @infinite="infiniteLoad">
                <span slot="no-more"><a href="" v-on:click.prevent.stop="scrollTop" class="w-100 d-block">↑</a></span>
                <span slot="no-results">-----データが存在しません-----</span>
            </infinite-loading>

        </div>

        <div class=" modal fade"  :class='{ "hiddenModalContent": !isModal }' id="modal-content" tabindex="-1" role="dialog" aria-labelledby="myExtraLargeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-xl">
                <div class="modal-content" v-if="targetOrder">
                    <div class="modal-header">

                        <div>
                            <h3>注文日時: {{ targetOrder.order_time }}</h3>
                            <div>総額: {{ targetOrder.total_price }}円</div>
                        </div>
                        <button type="button" class="close" data-dismiss="modal" v-on:click="closeModal" ref="closeModalContent">
                            <span>&times</span>
                        </button>
                    </div>
                    <div class="modal-body row ">
                        <div class="col-md-6" style="height: 90%">
                            <div class="scroll-wrapper" >
                                <div class="row mb-2" v-for="detail in targetOrder.order_details" :key="targetOrder.order_details.id">
                                    <div class="item-box col-md-2">
                                        <a >
                                            <img v-bind:src="detail.item.thumbnail" loading="lazy" alt=""  class="img-thumbnail h-auto" />
                                        </a>
                                    </div>
                                    <div class="col-md-9 row">
                                        <div class="item-box col-md-12">
                                            商品名: {{ detail.item.name }}
                                        </div>
                                        <div class="item-box col-md-12">
                                            点数: {{ detail.item_qty }}
                                        </div>
                                        <div class="item-box col-md-12">
                                            金額: {{ detail.unit_price }} × {{ detail.item_qty }} = {{ detail.sum_price }} 円
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" data-dismiss="modal" v-on:click="closeModal" >close</button>
                    </div>
                </div>
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
                orders: [],
                currentPage: 1,
                targetOrder: null,
                isModal: false
            }
        },
        created() {
        },
        mounted() {
        },
        computed: {
        },
        methods: {
            initTargetOrder(){
                this.targetOrder = {

                }
            },
            showDetail(order){
                this.targetOrder = order
                this.isModal = true;
            },
            closeModal(){
                //this.targetOrder = null
                this.isModal = false;
            },
            searchOrder(){
                let url = this.initData.getOrderDetailUrl
                    + '?page=' + this.currentPage;
                let self = this

                axios.get(url).then( response => {
                    let orders = response.data.orders.data
                    self.orders = [...self.orders, ...orders]
                    if(orders.length === 0){
                        self.$refs.infiniteLoading.stateChanger.complete();
                    } else {
                        self.$refs.infiniteLoading.stateChanger.loaded();
                    }

                    self.currentPage++

                }).catch(self.errorHandler);
            },
            infiniteLoad(){
                this.searchOrder()
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

                this.$refs.orderContent.scrollTo({
                    top: 0,
                    behavior: "smooth"
                })
            }
        }
    }
</script>

<style scoped>
    #order-content{
        height: 70vh;
        overflow-y: auto;
    }
</style>
