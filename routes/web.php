<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;

/*
|--------------------------------------------------------------------------
| Web Routes
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
Route::resource('posts', PostController::class);

/*
|--------------------------------------------------------------------------
| Revision History & Management (Feature 1 & 2)
|--------------------------------------------------------------------------
*/
Route::get('posts/{post}/revisions', [PostController::class, 'revisions'])
    ->name('posts.revisions');

Route::get('posts/{post}/revisions/export', [PostController::class, 'exportCsv'])
    ->name('posts.revisions.export');

Route::delete('posts/{post}/revisions/delete-all', [PostController::class, 'deleteAllRevisions'])
    ->name('posts.revisions.deleteAll');

// 1-Click Field Revert & Snapshot Rollback (Feature 1)
Route::post('posts/{post}/revisions/{revision}/revert-field', [PostController::class, 'revertField'])
    ->name('posts.revisions.revertField');

Route::post('posts/{post}/revisions/{revision}/rollback-snapshot', [PostController::class, 'rollbackSnapshot'])
    ->name('posts.revisions.rollbackSnapshot');

Route::get('posts/{post}/revisions/{revision}/preview-revert', [PostController::class, 'previewRevert'])
    ->name('posts.revisions.previewRevert');

// Visual Diff Studio & Arbitrary Comparison (Feature 2)
Route::get('posts/{post}/revisions/{revision}/diff', [PostController::class, 'revisionDiff'])
    ->name('posts.revisions.diff');

Route::get('posts/{post}/revisions/compare', [PostController::class, 'compareRevisions'])
    ->name('posts.revisions.compare');

/*
|--------------------------------------------------------------------------
| Time-Machine Snapshot Previewer (Feature 3)
|--------------------------------------------------------------------------
*/
Route::get('posts/{post}/time-machine', [PostController::class, 'timeMachine'])
    ->name('posts.timeMachine');

/*
|--------------------------------------------------------------------------
| Revision Analytics & Audit Report Studio (Feature 6)
|--------------------------------------------------------------------------
*/
Route::get('/revision-dashboard', [PostController::class, 'dashboard'])
    ->name('revision.dashboard');

Route::get('/revision-dashboard/audit-report', [PostController::class, 'auditReport'])
    ->name('revision.auditReport');
