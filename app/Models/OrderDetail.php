<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
    //
    protected $guarded = ['create_at', 'update_at',];

    protected $hidden = [
        self::CREATED_AT, self::UPDATED_AT,
    ];

    public function order(){
        return $this->hasOne(Order::class, 'id', 'order_id');
    }

    public function item(){
        return $this->hasOne(Item::class, 'id', 'item_id');
    }
}
