<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\WorkspaceController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Auth routes (Feature 1)
|--------------------------------------------------------------------------
*/


// More route groups (profile, workspaces, jobs, etc.) will be added
// here feature by feature, following the CreatorHub brief.

/*
|--------------------------------------------------------------------------
| Profile routes
|--------------------------------------------------------------------------


// Public - anyone can view another user's profile
Route::get('/users/{user}', [ProfileController::class, 'showUser']);

/*
|--------------------------------------------------------------------------
| Workspace routes (Epic 2) - all private, member-only
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/workspaces', [WorkspaceController::class, 'index']);
    Route::post('/workspaces', [WorkspaceController::class, 'store']);
    Route::get('/workspaces/{workspace}', [WorkspaceController::class, 'show']);
    Route::put('/workspaces/{workspace}', [WorkspaceController::class, 'update']);
    Route::delete('/workspaces/{workspace}', [WorkspaceController::class, 'destroy']);

    Route::get('/workspaces/{workspace}/members', [WorkspaceController::class, 'members']);
    Route::post('/workspaces/{workspace}/members', [WorkspaceController::class, 'addMember']);
    Route::delete('/workspaces/{workspace}/members/{user}', [WorkspaceController::class, 'removeMember']);

    /*
    |----------------------------------------------------------------------
    | Task routes (Epic 2)
    |----------------------------------------------------------------------
    */
    Route::get('/tasks', [TaskController::class, 'index']);
    Route::post('/tasks', [TaskController::class, 'store']);
    Route::put('/tasks/{task}', [TaskController::class, 'update']);
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy']);
    Route::patch('/tasks/{task}/move', [TaskController::class, 'move']);
});
