<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemTag extends Model
{
    protected $table = 'item_tag';
    protected $guarded = ['created_at', 'updated_at', ];
}
