<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\Request;

class TagController extends MasterBasicController
{
    public $facadeName = Tag::class;

    protected function setData(&$masterInstance, $request){
        $masterInstance->name = $request->name;
    }
}
