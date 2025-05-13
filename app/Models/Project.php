<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ProjectRegistered;

class Project extends Model
{
    use HasFactory;

    protected $connection = "MYSQLICONN";
    protected $table = "projects";

    protected $appends = array('checked','shipping_schedule_order');

    public function getCheckedAttribute()
    {
        return false;
    }

    public function getModelAttribute()
    {
        return false;
    }

    public function getShippingScheduleOrderAttribute()
    {
        return $this->hasMany('App\ShippingScheduleProjectData','project_id')->pluck('id')->toArray();
    }

    public function getNewSeqAttribute()
    {
        return $this->branch()->value('seq') . '-' . $this->seq;
    }

    public function setNameAttribute($value) {
        $this->attributes['name'] = ucwords($value);
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
    
    public function branch(){
        return $this->belongsTo(Branch::class, 'branch_id', 'branch_id');
    }

    
    public function Project_Color_Scheme()
    {
        return $this->hasMany('App\SpecSheetProjectColorScheme', 'project_id', 'id')
        ->LeftJoin('spec_sheet_color_schemes', 'spec_sheet_color_schemes.id', 'spec_sheet_project_color_schemes.color_scheme_id');
    }

    public function projectRegistered()
    {
        return $this->hasMany(ProjectRegistered::class, 'project_id');
            // ->select(
            //     'project_registered.project_id',
            //     'project_registered.id',
            //     'project_registered.lot',
            //     'project_registered.construction_code',
            //     'project_registered.house_code',
            //     'project_registered.shipping_flag',
            //     'project_designer_incharge.designer_id',
            //     'users.username',
            // )
            // ->join('project_designer_incharge','project_designer_incharge.project_id','project_registered.id')
            // ->join('users','users.id','project_designer_incharge.designer_id');
    }

    public function children()
    {
        return $this->projectRegistered();
    }

    public function project_registered_house_type(){
        return $this->hasMany('App\ProjectRegistered','project_id')
        ->select(
            'project_id',
            'house_specification_id as house_type_id',
            'house_types.name',
        )
        ->leftJoin('house_types', 'house_types.id', 'project_registered.house_specification_id')
        ->orderBy('house_types.name','ASC')
        ->groupBy('house_specification_id','project_id','house_types.name')
        ;
    }
}
