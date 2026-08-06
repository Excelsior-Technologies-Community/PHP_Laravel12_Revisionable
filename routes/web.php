<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;


/*
|--------------------------------------------------------------------------
| Redirect Home
|--------------------------------------------------------------------------
*/

Route::get('/', function () {

    return redirect()->route('posts.index');
});



/*
|--------------------------------------------------------------------------
| Posts CRUD Routes
|--------------------------------------------------------------------------
*/

Route::resource(
    'posts',
    PostController::class
);



/*
|--------------------------------------------------------------------------
| Revision History
|--------------------------------------------------------------------------
*/

Route::get(
    'posts/{post}/revisions',
    [PostController::class, 'revisions']
)
    ->name('posts.revisions');



/*
|--------------------------------------------------------------------------
| Export Revision CSV
|--------------------------------------------------------------------------
*/

Route::get(
    'posts/{post}/revisions/export',
    [PostController::class, 'exportCsv']
)
    ->name('posts.revisions.export');



/*
|--------------------------------------------------------------------------
| Delete All Revisions
|--------------------------------------------------------------------------
*/

Route::delete(
    'posts/{post}/revisions/delete-all',
    [PostController::class, 'deleteAllRevisions']
)
    ->name('posts.revisions.deleteAll');



/*
|--------------------------------------------------------------------------
| Revision Analytics Dashboard
|--------------------------------------------------------------------------
*/

Route::get(
    '/revision-dashboard',
    [PostController::class, 'dashboard']
)
    ->name('revision.dashboard');
