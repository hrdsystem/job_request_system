<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class viewDocumentHistory extends Model
{
    use HasFactory;

    
    protected $connection = 'mysql';
    protected $table = 'document_view_history';

    public function viewer(){
        return $this->belongsTo(IconnUser::class, 'user_id');
    }

    public function scopeForDocument($query, $uploadId, $requestId){
        return $query->where('upload_id', $uploadId)
                    ->where('request_id', $requestId);
    }

}
