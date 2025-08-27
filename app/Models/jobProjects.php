<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class jobProjects extends Model
{
    use HasFactory;

    protected $connection = 'MYSQLICONN';
    protected $table = 'projects';
}
