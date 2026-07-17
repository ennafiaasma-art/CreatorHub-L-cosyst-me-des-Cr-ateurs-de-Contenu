<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\WorkspaceController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\JobController;
use App\Http\Controllers\Api\JobApplicationController;

/*
|--------------------------------------------------------------------------
| Auth routes (Feature 1)
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->group(function () {
    // Public
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);

    // Protected (needs a valid Sanctum token)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
        Route::put('/profile', [AuthController::class, 'updateProfile']);
        Route::put('/change-password', [AuthController::class, 'changePassword']);
    });
});

/*
|--------------------------------------------------------------------------
| Portfolio Feed routes (Epic 1)
|--------------------------------------------------------------------------
*/
// Public browsing (feed is visible to anyone, no login needed to view/search)
Route::get('/projects', [ProjectController::class, 'index']);
Route::get('/projects/{project}', [ProjectController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/projects', [ProjectController::class, 'store']);
    Route::put('/projects/{project}', [ProjectController::class, 'update']);
    Route::delete('/projects/{project}', [ProjectController::class, 'destroy']);

    Route::post('/projects/{project}/like', [ProjectController::class, 'like']);
    Route::delete('/projects/{project}/like', [ProjectController::class, 'unlike']);

    Route::post('/projects/{project}/bookmark', [ProjectController::class, 'bookmark']);
    Route::delete('/projects/{project}/bookmark', [ProjectController::class, 'unbookmark']);

    Route::get('/bookmarks', [ProjectController::class, 'myBookmarks']);
});


// More route groups (profile, workspaces, jobs, etc.) will be added
// here feature by feature, following the CreatorHub brief.

/*
|--------------------------------------------------------------------------
| Profile routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);
});


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

    Route::apiResource('jobs', JobController::class);

Route::post('/jobs/{job}/apply',[
    JobApplicationController::class,
    'apply'
]);

Route::patch('/applications/{application}',[
    JobApplicationController::class,
    'updateStatus'
]);
});

