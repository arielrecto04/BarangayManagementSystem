<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BlotterController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\OfficialController;
use App\Http\Controllers\ResidentController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\DocumentRequestController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');



Route::post('/login', [AuthenticationController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout',  [AuthenticationController::class, 'logout']);

    Route::prefix('projects')->group(function () {
        Route::get('/search', [ProjectController::class, 'search']);
    });

    Route::prefix('document-requests')->group(function () {
        Route::get('/statistic', [DocumentRequestController::class, 'statistic']);
    });

    Route::apiResource('residents', ResidentController::class);
    Route::apiResource('officials', OfficialController::class);
    Route::apiResource('blotters', BlotterController::class);
    Route::apiResource('complaints', ComplaintController::class);
    Route::apiResource('projects', ProjectController::class);
    Route::apiResource('documents', DocumentController::class);
    Route::apiResource('document-requests', DocumentRequestController::class);

});
