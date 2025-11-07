<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class viewDocumentHistory extends Model
{
    use HasFactory;

    
    protected $connection = 'mysql';
    protected $table = 'document_view_history';
}
