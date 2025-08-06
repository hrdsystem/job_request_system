<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmailRecipient extends Model
{
    use HasFactory, SoftDeletes;

    protected $appends = ['checked', 'disabled'];

    public function getCheckedAttribute()
    {
        return false;
    }

    public function getDisabledAttribute()
    {
        return false;
    }
}
