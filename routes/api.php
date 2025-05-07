<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BugController;
use App\Http\Controllers\QaChecklistController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::apiResource('profiles', ProfileController::class);
    Route::apiResource('bugs', BugController::class);
    Route::apiResource('qa-checklists', QaChecklistController::class);
    Route::post('qa-checklists/{qaChecklist}/items', [QaChecklistController::class, 'addItem']);
    Route::post('qa-checklists/{qaChecklist}/responses', [QaChecklistController::class, 'submitResponse']);
    Route::get('qa-checklists/{qaChecklist}/responses', [QaChecklistController::class, 'getResponses']);
});
