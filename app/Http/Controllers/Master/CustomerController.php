<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerController extends MasterBasicController
{

    public $facadeName = Customer::class;

    protected function setData(&$masterInstance, $request){
        $masterInstance->user_id = $request->user_id;
        $masterInstance->customer_name = $request->customer_name;
        $masterInstance->tel_no = $request->tel_no;
        $masterInstance->post_no = $request->post_no;
        $masterInstance->address1 = $request->address1;
        $masterInstance->address2 = $request->address2;
        $masterInstance->address3 = $request->address3;
    }

    public function hasUserCustomer(){
        $user = Auth::user();
        if(!$user){
            return false;
        }
        if(Customer::where(['user_id' => $user->id])->count() > 0){
            return true;
        }
        return false;
    }

    public function create(){

        $user_id = (string)Auth::user()->id;

        $initData = [
            'getCustomerUrl' => route('customers.show', $user_id),
            'createCustomerUrl' => route('customers.store'),
            'updateCustomerUrl' => route('customers.update', $user_id),
        ];
        return view('customer.customerEdit', compact('initData'));
    }

    public function edit($user_id){

        if(!$this->canEditCustomer($user_id)){
            return response('error', 400);
        }

        $data = [
            'getCustomerUrl' => route('customers.show', $user_id),
            'createCustomerUrl' => route('customers.store'),
            'updateCustomerUrl' => route('customers.update', $user_id),
            'targetUserId' => Auth::user()->id,
            "redirectUrl" => request()->redirectUrl ?? ""
        ];
        return view('customer.customerEdit', compact('data'));

    }

    public function show($user_id){
        if(!$this->canEditCustomer($user_id)) {
            return response()->json(['error' => 'error'], 404);
        }

        return parent::show($user_id);

    }

    public function store(Request $request){

        if(!$this->canEditCustomer($request->user_id)) {
            return response()->json(['error' => 'error'], 404);
        }

        return parent::store($request);

    }

    public function update(Request $request, $user_id){
        if(!$this->canEditCustomer($request->user_id)) {
            return response()->json(['error' => 'error'], 404);
        }

        return parent::update($request, $user_id);
    }


    public function destroy($key)
    {

        if(!Auth::user()->hasRole('admin')){
            return response(['result' => 'ng'], 400);
        }

        return parent::destroy($key);
    }


    protected function findDataByKey($key){
        return $this->facadeName::where(['user_id' => $key])->first();
    }


    private function canEditCustomer($user_id){

        return ((string)Auth::user()->id === (string)$user_id) || Auth::user()->hasRole('admin');
    }

}
