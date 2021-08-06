<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemType extends Model
{

    protected $guarded = ['created_at', 'updated_at', ];

    public function items(){
        return $this->hasMany(Item::class, 'type_id', 'id');
    }
}
