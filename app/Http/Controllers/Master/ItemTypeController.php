<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\ItemType;

class ItemTypeController extends MasterBasicController
{

    public $facadeName = ItemType::class;

    protected function setData(&$masterInstance, $request){
        $masterInstance->name = $request->name;
    }
}
