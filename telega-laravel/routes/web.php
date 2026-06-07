<?php
require __DIR__.'/auth.php';

use App\Http\Controllers\Auth\GitHubAuthController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MessageController;


Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::controller(GitHubAuthController::class)->group(function () {
    Route::get('/auth/github/redirect', 'redirect')->name('github.redirect');
    Route::get('/auth/github/callback',  'callback')->name('github.callback');
});

Route::resource('/chat', ChatController::class);
