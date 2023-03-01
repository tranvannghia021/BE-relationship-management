<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SocialAuthController;
use App\Http\Controllers\VerifyController;


Route::group(['prefix'=>'','middleware'=>'auth.jwt'],function (){

});

Route::group(['prefix'=>'verify-email','middleware'=>'auth.verify'],function (){
    Route::get('',[VerifyController::class,'handleVerify']);
});
Route::post('{platform}/generate-url',[SocialAuthController::class,'generateUrl'])->where(['platform'=>'(github|facebook|google)']);
Route::get('platform/auth',[SocialAuthController::class,'auth'])->middleware('auth.social');
