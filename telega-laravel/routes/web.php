<?php

use App\Http\Controllers\Auth\GitHubAuthController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserFriendsController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('chat', ChatController::class);

    Route::get('friends', [UserFriendsController::class, 'friends'])->name('friends.index');
    Route::get('possible-friends', [UserFriendsController::class, 'possibleFriends'])->name('friends.possible');
    Route::post('friends', [UserFriendsController::class, 'addFriends'])->name('friends.add');
    Route::delete('friends', [UserFriendsController::class, 'removeFriends'])->name('friends.remove');
});

Route::prefix('chat/{chat}')->group(function () {
    Route::resource('message', MessageController::class);
});


Route::get('/auth/github', [GitHubAuthController::class, 'redirect'])->name('login.github');
Route::get('/auth/github/callback', [GitHubAuthController::class, 'callback']);

require __DIR__.'/auth.php';
