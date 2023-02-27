<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\SocialAuthController;


Route::group(['prefix'=>'','middleware'=>'auth.jwt'],function (){

});
Route::post('{platform}/generate-url',[SocialAuthController::class,'generateUrl']);
Route::get('platform/auth',[SocialAuthController::class,'auth'])->middleware('auth.social');
