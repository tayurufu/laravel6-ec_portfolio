<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\StockLocation;
use Illuminate\Http\Request;

class StockLocationController extends MasterBasicController
{
    public $facadeName = StockLocation::class;

    protected function setData(&$masterInstance, $request){
        $masterInstance->name = $request->name;
    }
}
