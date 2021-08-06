<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', 'HomeController@index')->name('index');

Route::get('/home', 'HomeController@index')->name('home');

//ログイン後アクセス可能な画面
Route::group(['middleware' => ['auth']], function () {

    Route::get('/logout', function(){
        Auth::logout();
        return redirect('/');
    });

    Route::get('/customers/edit/{user_id}', 'Master\CustomerController@edit')->name('customer.edit');

    Route::prefix('order')->group(function (){
        Route::get('/myCart/index', 'Order\CartController@showMyCart')->name('order.myCart');
        Route::get('showOrderHistory', 'Order\OrderController@showOrderHistoryPage')->name('order.history.index');
    });
});

// 外部認証用
Route::prefix('auth')->group(function() {

    Route::get('/{provider}', 'Auth\OAuthLoginController@socialOAuth')
        ->where('provider','google')
        ->name('socialOAuth');

    Route::get('/{provider}/callback', 'Auth\OAuthLoginController@handleProviderCallback')
        ->where('provider','google')
        ->name('oauthCallback');

});

// 商品画面
Route::prefix('item')->group(function() {

    Route::get('/', 'Item\ItemController@index')->name('item.index');
    Route::get('detail/{id}', 'Item\ItemController@detail')->name('item.detail');
    Route::get('photo/{filename}', 'Item\ItemController@getItemPhoto')->name('item.photo.get');

    // adminのみ商品情報編集可能
    Route::group(['middleware' => 'permission:edit_item'], function() {

        Route::get('edit/{id?}', 'Item\ItemController@edit')->name('item.edit');
    });

});

// adminのみアクセスコントロール編集可能
Route::group(['middleware' => 'role:admin'], function(){
   Route::prefix('acl')->group(function(){
       Route::get('/index', 'Admin\AclController@index')->name('admin.acl.index');
   }) ;
});




Route::get('myerror', function(){
    throw new Exception('myerror !!!');
});


Auth::routes();

