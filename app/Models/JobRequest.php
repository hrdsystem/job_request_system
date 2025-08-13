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
        return $this->hasMany('App\JobRequestRequirement', 'job_request_id');
    }

    public function uploaded_file(){
        return $this->hasMany('App\JobRequestUpload', 'request_id')
        ->select(
            'job_request_uploads.*',
            'job_request_uploaded_files.upload_id',
            'job_request_uploaded_files.orig_filename',
            'job_request_uploaded_files.file_hash',
        )
        ->leftJoin('job_request_uploaded_files', 'job_request_uploaded_files.upload_id', 'job_request_uploads.id');
    }
}
