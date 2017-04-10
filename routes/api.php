<?php
use Illuminate\Http\Request;

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

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');

Route::group(['prefix' => 'auth'], function () {
    Route::put('login', 'AuthenticateController@login');
    Route::put('register', 'AuthenticateController@register');
    Route::put('social', 'AuthenticateController@social');
});

// M A N A G E  P R O F I L E
Route::put('profile', 'RoomController@UpdateProfile')->middleware('api.auth');
Route::get('profile', 'RoomController@getProfile')->middleware('api.auth');

// M A N A G E  C A T E G O R I E S
Route::group(['prefix' => 'category'], function () {
    Route::post('', 'RoomController@createCategory');
    Route::get('', 'RoomController@getCategory');
});

Route::group(['prefix' => 'chat'], function () {
    Route::put('', 'ChatController@add')->middleware('api.auth');
    Route::get('', 'ChatController@read')->middleware('api.auth');
});

// G E T  A L L  R O O M S
Route::get('rooms', 'RoomController@getRooms');
Route::get('room/{roomID}', 'RoomController@getRoomByID');
Route::post('rooms', 'RoomController@createRoom');
Route::put('room/{roomID}', 'RoomController@editRoom');
// R o o m  S u b s c i p t i o n
Route::get('rooms/{roomID}/subscribers', 'RoomController@getRoomSubscribers');
Route::put('rooms/{roomID}/subscribe', 'RoomController@subscribeToRoom')->middleware('api.auth');
Route::post('rooms/{roomID}/unsubscribe', 'RoomController@leaveRoom');
// Get User Matches
Route::get('matches', 'RoomController@getUserMatches')->middleware('api.auth');;
Route::post('myrooms', 'RoomController@getUserRooms');
// H e l p e r s
Route::get('is/{userID}/in/{roomID}', 'RoomController@isUserInRoom');