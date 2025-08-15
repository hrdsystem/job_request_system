<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JobRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $appends = ['checked', 'disabled', 'requirements'];

    public function getCheckedAttribute()
    {
        return false;
    }

    public function getDisabledAttribute()
    {
        return false;
    }

    public function getRequirementsAttribute(){
        return $this->hasMany('App\Models\JobRequestRequirement', 'job_request_id')->pluck('document_id')->toArray();
    }

    public function attachments(){
        return $this->hasMany(JobAttachment::class);
    }

    public function requiredDocument(){
        return $this->hasMany('App\Models\JobRequestRequirement', 'job_request_id');
    }

    public function uploaded_file(){
        return $this->hasMany('App\Models\JobRequestUpload', 'request_id')
        ->select(
            'job_request_uploads.*',
            'job_request_uploaded_files.upload_id',
            'job_request_uploaded_files.orig_filename',
            'job_request_uploaded_files.file_hash',
        )
        ->leftJoin('job_request_uploaded_files', 'job_request_uploaded_files.upload_id', 'job_request_uploads.id');
    }

    public function requirements(){
        return $this->hasMany(JobRequestRequirement::class, 'job_request_id', 'id');
    }

    public function uploads(){
        return $this->hasMany(JobRequestUploadedFile::class, 'request_id', 'id');
    }

    public function scopeWithRequirementsData($query, $jobRequestIds){
        return $query
        ->select(
            'job_request_requirements.job_request_id',
            'job_request_requirements.document_id',
        )
        ->leftJoin('job_requests', 'job_requests.id', 'job_request_requirements.id')
        ->whereIn('id', $jobRequestIds)
        ->get();
    }

    public function scopeWithUploadedData($query, $uploadRequestIds){
        return $query
        ->select(
            'job_request_uploads.request_id',
            'job_request_uploads.document_id'
        )
        ->leftJoin('job_request_uploads', 'job_request_uploads.id', 'upload_id')
        ->leftJoin('job_requests', 'job_requests.id', 'job_request_uploads.request_id')
        ->whereIn('upload_id', $uploadRequestIds)
        ->get();
    }
}
