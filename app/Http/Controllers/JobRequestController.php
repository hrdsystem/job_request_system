<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\App;

use App\Models\JobRequestSubDocument;
use App\Models\JobRequired;
use App\Models\JobRequest;
use App\Models\IconnUser;
use Illuminate\Support\Facades\Date;

class JobRequestController extends Controller
{
    public function getJobRequests(Request $request){
        $cnt = JobRequest::when(request('search'), function ($q) {
            for($i=0;$i<count(request('search'));$i++){
                return $q->orWhere(request('search')[$i]['column'],'like','%'.request('search')[$i]['val'].'%');
            }
        })->count();

        $users = IconnUser::select('id', 'username');
    
        $data = JobRequest::joinSub($users, 'users', function($join){
            $join->on('job_requests.created_by', '=', 'users.id');
        })
        ->select(
            'job_requests.id',
            'job_requests.project_name',
            'job_requests.subject',
            'job_requests.lot_number',
            'job_requests.status',
            'job_requests.requested_date',
            'job_requests.job_ecd',
            'job_requests.note',
            'job_requests.created_at',
            'users.username',
            'job_requests.updated_at',
        )
        ->when(request('search'), function ($q) {
            for($i=0;$i<count(request('search'));$i++){
                return $q->orWhere(request('search')[$i]['column'],'like','%'.request('search')[$i]['val'].'%');
            }
        })
        ->when(request('sort') , function ($q) {
            for($i=0;$i<count(request('sort'));$i++){
                $q->orderBy(request('sort')[$i]['column'],request('sort')[$i]['val']);
            }
            return $q;
        })
        ->get();
        
        return [$data, $cnt];
    }

    public function jobRequestInsert(Request $request){
        // return $request;

        try{
            DB::beginTransaction();
            
            $data = new JobRequest;
            $data->project_name = $request->get('project_name');
            $data->subject = $request->get('subject');
            $data->lot_number = $request->get('lot_number');
            $data->status = 0;
            $data->requested_date = $request->get('requested_date');
            $data->job_ecd = now();
            $data->note = $request->get('note');
            $data->created_by = 271;
            $data->updated_by = 271;
            $data->save();

            $last_id = $data->id;

            if($request->get('documents') !=null) {
                $job_required_documents = json_decode($request->input('documents'));
                $arr = array();
                foreach($job_required_documents as $val){
                    $arr[] = array(
                        'job_request_id' => $last_id,
                        'document_id' => $val,
                        'created_at' => new \DateTime,
                        'updated_at' => new \DateTime
                    );
                }
                DB::table('job_request_requirements')->insert($arr);
            }

            DB::commit();
        } catch(\Exception $e){
            return $e->getMessage();
        }
        return $data;
    }

    public function jobRequestUpdate(Request $request){
        try{
            DB::beginTransaction();
            $data = JobRequest::find($request->get('id'));
            $data->project_name = $request->get('project_name');
            $data->subject = $request->get('subject');
            $data->lot_number = $request->get('lot_number');
            $data->status = $request->get('status');
            $data->requested_date = $request->get('requested_date');
            $data->job_ecd = now();
            $data->note = $request->get('note');
            $data->created_by = 271;
            $data->updated_by = 271;
            $data->updated_at = now();
            $data->save();

            $addJobRequirement = json_decode($request->get('addJobRequirement'));
            if(!empty($addJobRequirement)){
                foreach($addJobRequirement as $id){
                    $add[] = array(
                        "job_request_id" => $request->get('id'),
                        "document_id" => $id,
                        "updated_at" => now()
                    );
                }
                DB::table('job_request_requirements')->insert($add);
            }
            
            $removeJobRequirement = json_decode($request->get('removeJobRequirement'));
            if(!empty($removeJobRequirement)){
                DB::table('job_request_requirements')->where("job_request_id", $request->get('id'))
                    ->whereIn('document_id', $removeJobRequirement)
                    ->delete();
            }
            DB::commit();
            return response()->json(['Success' => 'Update Successful']);
        } catch(\Exception $e){
            return $e->getMessage();
        }
    }
}
