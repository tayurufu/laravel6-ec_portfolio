<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    //
    protected $guarded = ['create_at', 'update_at',];

    protected $hidden = [
        self::CREATED_AT, self::UPDATED_AT,
    ];


    public function customer(){
        return $this->hasOne(Customer::class, 'id', 'customer_id');
    }

    public function order_details(){
        return $this->hasMany(OrderDetail::class, 'order_id', 'id');
    }
}
