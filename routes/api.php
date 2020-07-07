<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
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

Route::post('slack/events', 'SlackController@actions');

Route::post('slack/menus', function (Request $request) {
    $challenge = $request->challenge;
    var_dump($challenge);
    return response()->json([
        'challenge' => $challenge
    ]);
});

Route::post('slack/action', 'SlackController@actions');

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
