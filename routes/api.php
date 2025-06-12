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
    Route::get('/users', [ProfileController::class, 'index']);

    // Bugs
    Route::apiResource('bugs', BugController::class);
    Route::post('bugs/{bug}/fix', [BugController::class, 'fixBug']);
    Route::apiResource('qa-checklists', QaChecklistController::class);
    Route::get('qa/stats', [QaChecklistController::class, 'getStats']);

    // QA Checklist Items
    Route::post('qa-checklists/{qaChecklist}/items', [QaChecklistController::class, 'addItem']);
    Route::put('qa-checklists/{qaChecklist}/items/{item}', [QaChecklistController::class, 'updateItem']);
    Route::delete('qa-checklists/{qaChecklist}/items/{item}', [QaChecklistController::class, 'deleteItem']);
    Route::get('qa-checklists/{qaChecklist}/active-items', [QaChecklistController::class, 'getActiveItems']);
    Route::get('qa-checklists/{qaChecklist}/completed-items', [QaChecklistController::class, 'getCompletedItems']);
    Route::post('qa-checklists/{qaChecklist}/responses', [QaChecklistController::class, 'submitResponse']);
    Route::get('qa-checklists/{qaChecklist}/responses', [QaChecklistController::class, 'getResponses']);

    // Teams
    Route::apiResource('teams', \App\Http\Controllers\Api\TeamController::class);
    Route::get('teams/{team}/members', [\App\Http\Controllers\Api\TeamController::class, 'getMembers']);
    Route::post('teams/{team}/members', [\App\Http\Controllers\Api\TeamController::class, 'addMember']);
    Route::delete('teams/{team}/members/{member}', [\App\Http\Controllers\Api\TeamController::class, 'removeMember']);
    Route::put('teams/{team}/members/{member}/role', [\App\Http\Controllers\Api\TeamController::class, 'updateMemberRole']);
    Route::get('teams/member/{memberId}', [\App\Http\Controllers\Api\TeamController::class, 'getTeamsByMemberId']);

    // Asana Tickets
    Route::post('asana-tickets', [\App\Http\Controllers\Api\AsanaTicketController::class, 'store']);
});
