<?php

// use Illuminate\Http\Request;
//
// /*
// |--------------------------------------------------------------------------
// | API Routes
// |--------------------------------------------------------------------------
// |
// | Here is where you can register API routes for your application. These
// | routes are loaded by the RouteServiceProvider within a group which
// | is assigned the "api" middleware group. Enjoy building your API!
// |
// */
//
// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::group([

    'prefix' => 'auth'

], function () {
    Route::post('login', 'AuthController@login');
    Route::post('logout', 'AuthController@logout');
    Route::post('refresh', 'AuthController@refresh');
    Route::post('me', 'AuthController@me');
    Route::post('payload', 'AuthController@payload');

});

 //---------------------------------------------------------------------------------------------------

 // USER api

 //List User
 Route::get('user', 'UserApiController@index');

 //list Single User
 Route::get('user/{id}', 'UserApiController@show');

 //Create new User
 Route::post('user/create', 'UserApiController@create');

 //Update  User
 Route::post('user/update/{id}', 'UserApiController@update');

 //Delete User
 Route::delete('user/delete/{id}', 'UserApiController@destroy');

//Check User
 Route::post('user/check', 'UserApiController@user_check');

 //Update  User_without_token
 Route::post('user/update-on-name/{id}', 'UserApiController@update_user_without_token');

 //Delete All data
 Route::delete('table/delete_allRecord', 'UserApiController@deleteAlldata');


  //---------------------------------------------------------------------------------------------------

  // product api

  //List product
  Route::get('product', 'ProductApiController@index');

    //List product
    Route::get('product/all', 'ProductApiController@product_list');

  //list Single product
  Route::get('product/{id}', 'ProductApiController@show');

  //Create new product
  Route::post('product/create', 'ProductApiController@create');

  //Update  product
  Route::post('product/update/{id}', 'ProductApiController@update');

  //Delete product
  Route::delete('product/delete/{id}', 'ProductApiController@destroy');

  //upload product image
  Route::post('product/image-upload', 'ProductApiController@product_image_upload');

  Route::get('/open_form','ProductApiController@open_form');

  //get latest price for produc

  Route::get('/latest-price-on-product', 'ProductApiController@getLatestPriceOnProduct');

  Route::get('/range','ProductApiController@range_test');

  Route::post('product/create/list', 'ProductApiController@add_Product_Record');

  //List product
  Route::get('getproduct', 'ProductApiController@getproduct');

  Route::get('Removeallproduct', 'ProductApiController@Removeallproduct');
  //---------------------------------------------------------------------------------------------------

  // product Range api

  //List product_range
  Route::get('product_range', 'Product_Range_ApiController@index');

  //list Single product_range
  Route::get('product_range/{id}', 'Product_Range_ApiController@show');

  //Create new product_range
  Route::post('product_range/create', 'Product_Range_ApiController@create');

  //Update  product_range
  Route::post('product_range/update/{id}', 'Product_Range_ApiController@update');

  //Delete product_range
  Route::delete('product_range/delete/{id}', 'Product_Range_ApiController@destroy');



  //---------------------------------------------------------------------------------------------------

  // purchase  api

  //List purchase
  Route::get('purchase', 'Purchase_ApiController@index');

  //list Single purchase
  Route::get('purchase/{id}', 'Purchase_ApiController@show');

  //Create new purchase
  Route::post('purchase/create', 'Purchase_ApiController@create');

  //Update  purchase
  Route::post('purchase/update/{id}', 'Purchase_ApiController@update');

  //Delete purchase
  Route::delete('purchase/delete/{id}', 'Purchase_ApiController@destroy');


  //---------------------------------------------------------------------------------------------------

  // Balance  api

  //List balance
  Route::get('balance', 'Balance_ApiController@index');

  //list Single balance
  Route::get('balance/{id}', 'Balance_ApiController@show');

  //Create new balance
  Route::post('balance/create', 'Balance_ApiController@create');

  //Update  balance
  Route::post('balance/update/{id}', 'Balance_ApiController@update');

  //Delete balance
  Route::delete('balance/delete/{id}', 'Balance_ApiController@destroy');

  //List  balance by date
  Route::post('balance-on-date', 'Balance_ApiController@list_balance_on_date');

  //List  balance by date
  Route::post('balance-on-date', 'Balance_ApiController@list_balance_on_date');




  //---------------------------------------------------------------------------------------------------

  // Export Purchase api

  //List Export Purchase
  Route::post('Export-Purchase', 'ExportPurchase_ApiController@Export_Purchase_file');

  //List  Purchase by date
  Route::post('Purchase-on-date', 'ExportPurchase_ApiController@list_Purchase_on_date');

 //List export purchase orders between two dates ProductReport.
  Route::post('PurchaseProductReport', 'ExportPurchase_ApiController@PurchaseProductReport');

  //List  Purchase by date on sum
  Route::post('PurchaseCostonDate', 'ExportPurchase_ApiController@PurchaseCostonDate');

  //List  Purchase by month year on sum
  Route::post('PurchaseCostonMonthYear', 'ExportPurchase_ApiController@PurchaseCostonMonthYear');

 //List  Purchase Retail Report
  Route::post('PurchaseRetailReport', 'ExportPurchase_ApiController@PurchaseRetailReport');

    //List  Purchase ReviewSellingPrice Report
  Route::post('ReviewSellingPrice', 'ExportPurchase_ApiController@ReviewSellingPrice');

  //---------------------------------------------------------------------------------------------------

  // expense api

  //List expense
  Route::post('expense', 'Expense_ApiController@index');

  //list Single expense
  Route::get('expense/{id}', 'Expense_ApiController@show');

  //Create new expense
  Route::post('expense/create', 'Expense_ApiController@create');

  //Update  expense
  Route::post('expense/update/{id}', 'Expense_ApiController@update');

  //Delete expense
  Route::delete('expense/delete/{id}', 'Expense_ApiController@destroy');

  //expense Report
  Route::post('expenseReport', 'Expense_ApiController@expenseReport');

 //expense Report
 Route::post('testApi', 'ExportPurchase_ApiController@test_api');

 Route::post('timezone_opration', 'ExportPurchase_ApiController@timezone_opration');
 



 


