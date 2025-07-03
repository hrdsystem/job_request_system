<?php

use App\Http\Controllers\JobRequestController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SpaController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [SpaController::class, 'job_request'])->where('any', '.*');

Route::get('/iconnsystem/job_request', [SpaController::class, 'job_request'])->where('any', '.*');

Route::get('/iconnsystem/job_request_settings/{any?}', [SpaController::class, 'job_request_settings'])->where('any', '.*');

Route::prefix('job_requests')->group(function(){
    Route::get('/attachments/{attachment_id}/{filename}', [JobRequestController::class, 'openAttachment']);
});

// Route::get('/job_request/attachments/{id}/{filename}', function ($id, $filename) {
//     $path = storage_path("app/public/job_request/{$id}/{$filename}"); // Adjust the path as necessary
//     if (!file_exists($path)) {
//         abort(404);
//     }
//     return response()->file($path);
// })->where('filename', '.*');

