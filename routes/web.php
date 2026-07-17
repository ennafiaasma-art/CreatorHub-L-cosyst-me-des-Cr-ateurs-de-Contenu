<?php

use Illuminate\Support\Facades\Route;

// CreatorHub is a pure REST API tested via Swagger/Postman.
// This route just confirms the app is running.
Route::get('/', function () {
    return response()->json([
        'message' => 'CreatorHub API is running.',
        'docs' => url('/api/documentation'),
    ]);
});
