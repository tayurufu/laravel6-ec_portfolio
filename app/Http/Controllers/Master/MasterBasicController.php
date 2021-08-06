<?php


namespace App\Http\Controllers\Master;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

abstract class MasterBasicController extends Controller
{
    /**
     * Facade名::class
     * @var
     */
    public $facadeName;


    /**
     * 全件取得
     * @return mixed
     */
    public function index()
    {
        return $this->facadeName::all();
    }

    /**
     *
     */
    public function create()
    {
        //
    }

    /**
     * 新規登録
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $masterClass = $this->createInstance();
        $this->setData($masterClass, $request);

        $masterClass->save();
        return response()->json(['result' => 'ok',  $this->getMasterClassName($masterClass) => $masterClass]);

    }

    /**
     * 主キーに該当するデータ1件取得
     * @param $key
     * @return mixed
     */
    public function show($key)
    {
        return $this->findDataByKey($key);
    }

    public function edit($id)
    {
        //
    }

    /**
     * 更新
     * @param Request $request
     * @param $key
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function update(Request $request, $key)
    {
        $masterClass = $this->findDataByKey($key);

        if(!$masterClass){
            return response(['result' => 'ng'], 400);
        }

        $this->setData($masterClass, $request);
        $masterClass->save();
        return response()->json(['result' => 'ok', $this->getMasterClassName($masterClass) => $masterClass]);
    }

    /**
     * 削除
     * @param $key
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function destroy($key)
    {
        $masterClass = $this->findDataByKey($key);

        if(!$masterClass){
            return response(['result' => 'ng'], 400);
        }

        $masterClass->delete();
        return response()->json(['result' => 'ok']);
    }


    protected abstract function setData(&$masterInstance, $request);


    protected function createInstance(){
        return new $this->facadeName();
    }

    protected function findDataByKey($key){
        return $this->facadeName::find($key);
    }

    protected function getMasterClassName($obj){
        return array_slice(explode('\\', get_class($obj)), -1)[0];
    }

}
