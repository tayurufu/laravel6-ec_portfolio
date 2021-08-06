<?php
/**
 * webログイン認証後のapi
 *
 * RouteServiceProviderと
 * Http\Kernel
 * で設定
 */


Route::get('items', 'Item\ItemController@getPaginateItem')->name('items.get');
Route::post('addCart/{id}', 'Order\CartController@addCartItem')->name('item.cart.add');
Route::post('removeCart/{id}', 'Order\CartController@removeCartItem')->name('item.cart.remove');


Route::group(['middleware' => ['auth']], function () {

    // 商品
    Route::prefix('item')->group(function() {

        // adminのみ商品情報編集可能
        Route::group(['middleware' => 'permission:edit_item'], function() {
            Route::post('store', 'Item\ItemController@store')->name('item.store');
            Route::delete('delete/{id}', 'Item\ItemController@delete')->name('item.delete');
        });

    });

    // 注文
    Route::prefix('order')->group(function (){
        Route::post('/myCart/removeCartItems', 'Order\CartController@removeCartItems')->name('order.myCart.removeCartItems');
        Route::post('/myCart/buyItems', 'Order\CartController@buyCartItems')->name('order.myCart.buyCartItems');
        Route::get('/myCart/items', 'Order\CartController@getCartItems')->name('order.myCart.items');

        Route::get('/history', 'Order\OrderController@getOrderHistories')->name('order.history.all');
        Route::get('/history/paginate/{page}', 'Order\OrderController@getOrderHistoriesPaginate')->name('order.history.paginate');
        Route::get('/history/{customerId}', 'Order\OrderController@showOrderHistory')->name('order.history.show');
    });

    // 管理者用
    Route::group(['middleware' => 'role:admin'], function() {
        Route::prefix('acl')->group(function (){
            //user
            Route::get('/users', 'Admin\AclController@getUsers')->name('acl.users.get');
            Route::get('/user/{id}', 'Admin\AclController@getUser')->name('acl.user.get');
            Route::put('/user/{id}', 'Admin\AclController@grant')->name('acl.user.grant');

            //role
            Route::get('/roles', 'Admin\AclController@getRoles')->name('acl.roles.get');
            Route::get('/role/{id}', 'Admin\AclController@getRole')->name('acl.role.get');
            Route::post('/role', 'Admin\AclController@createRole')->name('acl.role.post');
            Route::put('/role/{id}', 'Admin\AclController@updateRole')->name('acl.role.put');
            Route::delete('/role/{id}', 'Admin\AclController@deleteRole')->name('acl.role.delete');

            //permission
            Route::get('/permissions', 'Admin\AclController@getPermissions')->name('acl.permissions.get');
            Route::get('/permission/{id}', 'Admin\AclController@getPermission')->name('acl.permission.get');
            Route::post('/permission', 'Admin\AclController@createPermission')->name('acl.permission.post');
            Route::put('/permission/{id}', 'Admin\AclController@updatePermission')->name('acl.permission.put');
            Route::delete('/permission/{id}', 'Admin\AclController@deletePermission')->name('acl.permission.delete');
        });
    });

    // マスタ変更
   Route::group(['middleware' => 'permission:edit_master'], function() {
        Route::resource('itemTypes', 'Master\ItemTypeController', ['except' => ['edit', 'create']]);
        Route::resource('tags', 'Master\TagController', ['except' => ['edit', 'create']]);
        Route::resource('stockLocations', 'Master\StockLocationController', ['except' => ['edit', 'create']]);

   });

   Route::resource('customers', 'Master\CustomerController', ['except' => ['edit', 'create', 'destroy']]);
    Route::prefix('customers')->group(function (){
    });


});

Route::get('myerror', function(){
    throw new Exception('my error!!!');
});


