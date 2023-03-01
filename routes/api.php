<?php

use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::post('register/user', [UserController::class, 'register']);
Route::post('login/user', [UserController::class, 'login']);
Route::post('forgot/password',  [UserController::class, 'forgotPassword']);
Route::post('verification/code/check', [UserController::class, 'verificationCodeCheck']);
Route::post('password/reset', [UserController::class, 'resetPassword']);


Route::middleware('auth:sanctum')->group(
    function () {
        Route::post('/logout', [UserController::class, 'logout']);

           
    }

);
