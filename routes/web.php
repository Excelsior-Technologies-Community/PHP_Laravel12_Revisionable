<?php

use App\Http\Controllers\PostController;
use Illuminate\Support\Facades\Route;

// Redirect root URL to posts page
Route::get('/', function () {
    return redirect()->route('posts.index');
});

// Posts CRUD Routes
Route::resource('posts', PostController::class);

// Additional route for revisions
Route::get('posts/{post}/revisions', [PostController::class, 'revisions'])
    ->name('posts.revisions');
