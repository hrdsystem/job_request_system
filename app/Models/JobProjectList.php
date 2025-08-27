<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobProjectList extends Model
{
    use HasFactory;

    protected $connection = 'MYSQLICONN';
    protected $table = 'project_registered';
}
