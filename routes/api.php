<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BugController;
use App\Http\Controllers\QaChecklistController;
use App\Http\Controllers\TeamController;

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

    // Bugs
    Route::apiResource('bugs', BugController::class);
    Route::post('bugs/{bug}/fix', [BugController::class, 'fixBug']);
    Route::apiResource('qa-checklists', QaChecklistController::class);

    // QA Checklist Items
    Route::post('qa-checklists/{qaChecklist}/items', [QaChecklistController::class, 'addItem']);
    Route::put('qa-checklists/{qaChecklist}/items/{item}', [QaChecklistController::class, 'updateItem']);
    Route::delete('qa-checklists/{qaChecklist}/items/{item}', [QaChecklistController::class, 'deleteItem']);
    Route::get('qa-checklists/{qaChecklist}/active-items', [QaChecklistController::class, 'getActiveItems']);
    Route::get('qa-checklists/{qaChecklist}/completed-items', [QaChecklistController::class, 'getCompletedItems']);
    Route::post('qa-checklists/{qaChecklist}/responses', [QaChecklistController::class, 'submitResponse']);
    Route::get('qa-checklists/{qaChecklist}/responses', [QaChecklistController::class, 'getResponses']);

    // Teams
    Route::apiResource('teams', TeamController::class);
    Route::post('teams/{team}/invite', [TeamController::class, 'invite']);
    Route::post('teams/invitations/{token}/accept', [TeamController::class, 'acceptInvitation']);
    Route::post('teams/invitations/{token}/decline', [TeamController::class, 'declineInvitation']);
    Route::delete('teams/{team}/members/{member}', [TeamController::class, 'removeMember']);
    Route::put('teams/{team}/members/{member}/role', [TeamController::class, 'updateMemberRole']);
});
