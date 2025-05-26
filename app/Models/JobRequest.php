<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobRequest extends Model
{
    use HasFactory;

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
}
