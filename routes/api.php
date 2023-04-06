<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SocialAuthController;
use App\Http\Controllers\VerifyController;
use App\Http\Controllers\UserController;


Route::group(['prefix'=>'account'],function (){
    Route::post('register',[UserController::class,'register']);
    Route::post('login',[UserController::class,'login']);
    Route::post('forgot',[UserController::class,'forgot']);
    Route::post('refresh-token',[UserController::class,'refreshToken'])->middleware('authJwt.refresh_token');
});

Route::group(['prefix'=>'','middleware'=>'auth.jwt'],function (){
    Route::get('account',[UserController::class,'users']);
});

Route::group(['prefix'=>'','middleware'=>'auth.verify'],function (){
    Route::get('verify-email',[VerifyController::class,'handleVerify']);
    Route::get('verify-forgot-password',[VerifyController::class,'handleVerifyForgotPassword']);
});
Route::post('{platform}/generate-url',[SocialAuthController::class,'generateUrl'])->where(['platform'=>'(github|facebook|google)']);
Route::get('platform/auth',[SocialAuthController::class,'auth'])->middleware('auth.social');
