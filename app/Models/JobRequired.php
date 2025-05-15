<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JobRequired extends Model
{
    use HasFactory, SoftDeletes;

    protected $appends = array('checked', 'disabled');   

    public function getCheckedAttribute(){
        return false;
    }

    public function getDisabledAttribute(){
        return false;
    }

    public static function NewReSeq($seq){
        self::where('seq','>=',$seq)->increment('seq');
        return $seq;
    }
    
    public static function EditSeq($old,$seq){
        if($old > $seq){
            self::whereBetween('seq', [$seq, $old-1])->increment('seq');
        }else{
            self::whereBetween('seq', [$old+1, $seq])->decrement('seq');
        }
        return $seq;
    }
    public static function Reseq(){
        $cnt = self::orderBy('seq','asc')->get();
        $i = 1;
        foreach($cnt as $key => $val){
            self::where('id',$cnt[$key]->id)->update(['seq' => $i]);
            $i++;
        }
    }

    public function sub_docs(){
        return $this->hasMany('App\JobRequestSubDocument', 'document_id', 'id')->where('active', 1);
    }
}
