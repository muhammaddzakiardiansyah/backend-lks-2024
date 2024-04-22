<?php

use App\Http\Controllers\Api\AtuhController;
use App\Http\Controllers\Api\FormController;
use App\Http\Controllers\Api\QuestionController;
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

Route::prefix('/v1')->group(function()
{
    Route::controller(AtuhController::class)->group(function()
    {
        Route::post('/auth/login', 'login');
        Route::post('/auth/logout', 'logout')->middleware('auth:sanctum');
    });
    Route::controller(FormController::class)->middleware('auth:sanctum')->group(function()
    {
        Route::post('/forms', 'createForm');
        Route::get('/forms', 'getAllForms');
        Route::get('/forms/{slug}', 'getDetailForm');
    });
    Route::controller(QuestionController::class)->middleware('auth:sanctum')->group(function()
    {
        Route::post('/forms/{slug}/questions', 'createQuestion');
    });
});
