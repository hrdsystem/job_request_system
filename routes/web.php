<?php

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

Route::get('/job_request', [SpaController::class, 'job_request'])->where('any', '.*');

Route::get('/job_request_settings/{any?}', [SpaController::class, 'job_request_settings'])->where('any', '.*');

