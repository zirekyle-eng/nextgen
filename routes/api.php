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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

// API Routes for Advisory Conversations (Real-time messaging)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/advisory/{conversationId}/messages', 'MyParent\AdvisoryCornerController@getMessages');
    Route::get('/advisory/{conversationId}/messages', 'SupportTeam\AdvisoryManagementController@getMessages');
});

// API Routes for Students Approval
Route::get('/classes/{classId}/sections', 'StudentController@getSections');

// BigBlueButton webhook endpoint (camera activity events, meeting lifecycle)
Route::post('/bbb/webhook', 'BigBlueButtonWebhookController@handle')->name('api.bbb.webhook');
