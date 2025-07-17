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
        Route::post('job_delete', 'jobRequestDelete');
        Route::post('job_status_change', 'jobRequestStatusChange');
        Route::post('get_required_documents', 'getRequiredDocuments');
        Route::post('master_users' , 'masterUsers');
        Route::post('process_job_updates', 'process_job_updates');
        Route::post('get_uploaded_requirements', 'getUploadedDocuments');
        Route::post('upload_history', 'upload_history');
        // Route::get('/attachments/{attachment_id}/{file_name}', 'openAttachment');
    });
});


// Route::post('/{attachment_id}/{file_name}', [JobRequestController::class, 'openAttachment']);


Route::prefix('/jobMaster')->group(function(){
    Route::controller(JobRequestSettingsController::class)->group(function (){
        Route::post('get_job_requireds', 'getJobRequired');
        Route::post('update_job_required', 'jobRequiredUpdate');
        Route::post('insert_job_required', 'jobRequiredInsert');
        Route::post('delete_job_required', 'jobRequiredDelete');
        Route::post('get_email_recipients', 'getEmailRecipient');
        Route::post('insert_recipients', 'insertJobRecipients');
    });
});

Route::controller(ProjectController::class)->group(function(){
    Route::get('project_drawer', 'get_project_drawer');
});
