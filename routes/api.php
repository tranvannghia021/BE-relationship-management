<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SocialAuthController;
use App\Http\Controllers\VerifyController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RelationShipController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\TagsController;


Route::group(['prefix'=>'account'],function (){
    Route::post('register',[UserController::class,'register']);
    Route::patch('re-send-link',[UserController::class,'reSendLinkVerifyEmail']);
    Route::post('login',[UserController::class,'login']);
    Route::post('forgot',[UserController::class,'forgot']);
    Route::post('refresh-token',[UserController::class,'refreshToken'])->middleware('authJwt.refresh_token');
});

Route::group(['prefix'=>'','middleware'=>'auth.jwt'],function (){
    Route::get('account',[UserController::class,'users']);
    Route::post('account',[UserController::class,'updateUsers']);
    Route::put('account/change-password',[UserController::class,'changePassword']);
    Route::delete('account',[UserController::class,'deleteUser']);

    Route::group(['prefix'=>'relationship'],function (){
        Route::get('',[RelationShipController::class,'getList']);
        Route::get('{id}',[RelationShipController::class,'getDetail']);
        Route::post('create',[RelationShipController::class,'createPeople']);
        Route::post('{id}',[RelationShipController::class,'updatePeople']);
        Route::delete('{id}',[RelationShipController::class,'deletePeople']);
    });

    Route::group(['prefix'=>'appointment'],function (){
        Route::get('',[AppointmentController::class,'getList']);
        Route::get('/{id}',[AppointmentController::class,'getDetail']);
        Route::post('/create',[AppointmentController::class,'createAppointment']);
        Route::post('/{id}',[AppointmentController::class,'update']);
        Route::delete('/{id}',[AppointmentController::class,'delete']);
    });


    Route::group(['prefix'=>'tags'],function (){
        Route::get('',[TagsController::class,'index']);
        Route::get('{id}',[TagsController::class,'show']);
        Route::post('create',[TagsController::class,'store']);
        Route::put('{id}',[TagsController::class,'update']);
        Route::delete('{id}',[TagsController::class,'destroy']);
    });
});

Route::group(['prefix'=>'','middleware'=>'auth.verify'],function (){
    Route::get('verify-email',[VerifyController::class,'handleVerify']);
    Route::get('verify-forgot-password',[VerifyController::class,'handleVerifyForgotPassword']);
});
Route::post('{platform}/generate-url',[SocialAuthController::class,'generateUrl'])->where(['platform'=>'(github|facebook|google)']);
Route::get('platform/auth',[SocialAuthController::class,'auth'])->middleware('auth.social');
