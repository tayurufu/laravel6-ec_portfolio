<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    //
    //protected $guarded = ['create_at', 'update_at', ];

    public function items(){
        return $this->belongsToMany(Item::class, 'item_tag', 'tag_id', 'item_id', 'id', 'id');
    }
}
