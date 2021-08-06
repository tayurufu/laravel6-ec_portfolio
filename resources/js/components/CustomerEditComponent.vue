<template>
    <div class="p-3">
        <form @submit.prevent  method="post" class="">
            <input type="hidden" name="user_id" v-model="customer.id" >

            <h2 class="my-3">送り先情報編集</h2>

            <div class="form-group row">
                <label class="col-md-3 col-form-label">名前: </label>
                <input class="form-control col-md-9" type="text" id="customer-name" name="customer_name" v-model="customer.customer_name">
            </div>
            <div class="form-group row">
                <label class="col-md-3 col-form-label">電話番号: </label>
                <input class="form-control col-md-9" type="text" id="customer-tel-no" name="tel_no" v-model="customer.tel_no" >
            </div>
            <div class="form-group row">
                <label class="col-md-3 col-form-label">郵便番号: </label>
                <input class="form-control col-md-9" type="text" id="customer-post-no" name="post_no" v-model="customer.post_no" >
            </div>
            <div class="form-group row">
                <label class="col-md-3 col-form-label">住所1: </label>
                <input class="form-control col-md-9" type="text" id="customer-address1" name="address1" v-model="customer.address1" >
            </div>
            <div class="form-group row">
                <label class="col-md-3 col-form-label">住所2: </label>
                <input class="form-control col-md-9" type="text" id="customer-address2" name="address2" v-model="customer.address2" >
            </div>
            <div class="form-group row">
                <label class="col-md-3 col-form-label">住所3: </label>
                <input class="form-control col-md-9" type="text" id="customer-address3" name="address3" v-model="customer.address3" >
            </div>


            <button class="btn btn-primary my-3" type="button" v-on:click="submitData()">確定</button>

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
                customer: {
                    id: "",
                    user_id: "",
                    customer_name: "",
                    tel_no: "",
                    post_no: "",
                    address1: "",
                    address2: "",
                    address3: "",
                },
                targetUserId: "",
                redirectUrl: "",
            }
        },
        created() {
        },
        mounted() {
            this.targetUserId = this.initData.targetUserId
            this.redirectUrl = this.initData.redirectUrl
            this.getCustomer()
        },
        computed: {

        },
        methods: {
            getCustomer(){
                let self = this
                axios.get(this.initData.getCustomerUrl)
                    .then(response => {

                        let tmpCustomer = response.data
                        if(tmpCustomer && tmpCustomer.id){
                            self.customer = tmpCustomer
                        } else {
                            self.customer.user_id = self.targetUserId
                        }

                    })
                    .catch(self.errorHandler)
            },
            submitData(){

                let self = this
                let params = this.customer
                if(!this.customer.id || this.customer.id === ""){
                    axios.post(this.initData.createCustomerUrl, params)
                        .then(response => {

                            alert('送り先情報を登録しました。')
                            if(self.redirectUrl && self.redirectUrl !== ""){
                                location.href = self.redirectUrl
                            } else {
                                self.getCustomer()
                            }

                        })
                        .catch(self.errorHandler)
                } else {
                    axios.put(this.initData.updateCustomerUrl, params)
                        .then(response => {

                            alert('送り先情報を更新しました。')
                            if(self.redirectUrl && self.redirectUrl !== ""){
                                location.href = self.redirectUrl
                            } else {
                                self.getCustomer()
                            }

                        })
                        .catch(self.errorHandler)
                }
            },
            backPage(){

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

</style>
