<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobRequestRequirement extends Model
{
    use HasFactory;

    public function uploads()
    {
        return $this->hasMany(JobRequestUpload::class, 'request_id');
    }
}
