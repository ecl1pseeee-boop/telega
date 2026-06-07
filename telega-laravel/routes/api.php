<?php

use App\Http\Controllers\MessageController;
use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return ['message' => 'API is working!'];
});

Route::prefix('chat/{chat}')->group(function () {
   Route::resource('message', MessageController::class);
});
