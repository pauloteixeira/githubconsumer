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

Route::get('/', function () {
    return view('welcome');
});

Route::get("/publisher", "BrokerController@index")->name("publish.message");
Route::get("/receive", "BrokerController@consumerUsersQueue")->name("receive.user.message");
Route::get("/receiveMessages", "BrokerController@consumerMessageQueue")->name("receive.messages");
Route::get("/github", "BrokerController@requestGitHub")->name("github.request");
