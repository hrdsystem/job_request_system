<?php

use App\Http\Controllers\JobRequestController;
use App\Http\Controllers\JobRequestSettingsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProjectController;
use App\Models\JobRequest;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('/jobRequest')->group(function(){
    Route::controller(JobRequestController::class)->group(function (){
        Route::post('get_job_request', 'getJobRequests');
        Route::post('job_insert', 'jobRequestInsert');
        Route::post('job_update', 'jobRequestUpdate');
    });
});

Route::prefix('/jobMaster')->group(function(){
    Route::controller(JobRequestSettingsController::class)->group(function (){
        Route::post('get_job_requireds', 'getJobRequired');
        Route::post('update_job_required', 'jobRequiredUpdate');
        Route::post('insert_job_required', 'jobRequiredInsert');
        Route::post('delete_job_required', 'jobRequiredDelete');
    });
});

Route::controller(ProjectController::class)->group(function(){
    Route::get('project_drawer', 'get_project_drawer');
});
