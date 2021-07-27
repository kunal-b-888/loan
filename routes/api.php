<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\v1\AuthController;
use App\Http\Controllers\api\v1\LoansController;
use App\Http\Controllers\api\v1\RepaymentsController;
use App\Http\Controllers\api\v1\ApiController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['middleware' => ['cors']], function () {
    Route::post('login', [AuthController::class,'login'])->name('login.api');
    Route::post('register', [AuthController::class,'register'])->name('register.api');

});
Route::group([
    
    'prefix' => 'v1', // URL Prefix
    'namespace' => 'api\v1', // For Controller
    'middleware' => ['auth:api'],
], function () { 
        Route::group(['prefix' => 'loan'], function () {
            Route::post('', [LoansController::class,'index'])->name('loans');
            Route::post('loans/create', [LoansController::class,'create'])->name('loans.create');
            Route::post('repayments/create', [RepaymentsController::class,'create'])->name('repayments.create');
        });
});
