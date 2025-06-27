<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobRequestUpload extends Model
{
    use HasFactory;

    public function files(){
        return $this->hasMany(JobRequestUploadedFile::class, 'upload_id');
    }
}
