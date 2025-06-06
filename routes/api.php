<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\Authcontroller;

Route::controller(Authcontroller::class)->group(function () {
    //auth routes
    Route::post('/register', 'register')->name('api.auth.register');
    Route::post('/login', 'login')->name('api.auth.login');
    
    //verification routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/otp', 'otp')->name('api.auth.otp'); 
        Route::post('/verify', 'verify')->name('api.auth.verify'); 
    });
    
    //password reset routes
    Route::post('/rest/otp', 'restOtp')->name('api.auth.rest.otp'); 
    Route::post('/rest/password', 'restPassword')->name('api.auth.rest.password'); 
});