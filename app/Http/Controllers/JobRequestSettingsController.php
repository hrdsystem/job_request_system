<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\App;

use App\Models\EmailRecipient;
use App\Models\JobRequestSubDocument;
use App\Models\JobRequired;
use App\Models\IconnUser;

class JobRequestSettingsController extends Controller
{

    public function getJobRequired(Request $request){
        $cnt = JobRequired::when(request('search'), function ($q) {
            for($i=0;$i<count(request('search'));$i++){
                return $q->orWhere(request('search')[$i]['column'],'like','%'.request('search')[$i]['val'].'%');
            }
        })->count();

        $users = IconnUser::select('id', 'username');
    
        $data = JobRequired::joinSub($users, 'users', function($join){
            $join->on('job_requireds.created_by', '=', 'users.id');
        })
        ->select(
            'job_requireds.id',
            'job_requireds.seq',
            'job_requireds.required_name',
            'job_requireds.filling_mark',
            'job_requireds.header_name',
            'job_requireds.created_at',
            'users.username',
            'job_requireds.updated_at',
        )
        ->with('sub_docs')
        ->when(request('search'), function ($q) {
            for($i=0;$i<count(request('search'));$i++){
                $q->where(request('search')[$i]['column'],'like','%'.request('search')[$i]['val'].'%');
            }
            return $q;
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

    
    public function jobRequiredInsert(Request $request){
        // return $request;

        $required_name_exists = JobRequired::where('required_name',$request->get('required_name'))->exists();
        $filling_mark_exists = JobRequired::where('filling_mark',$request->get('filling_mark'))->exists();
        $header_name_exists = JobRequired::where('header_name',$request->get('header_name'))->exists();
        if($required_name_exists){
            return 1;
        } else if($filling_mark_exists){
            return 2;
        } else if ($header_name_exists){
            return 3;
        } else{
            try{
                DB::beginTransaction();
                $data = new JobRequired;
                $data->seq = JobRequired::NewReSeq($request->get('seq'));
                $data->required_name = $request->get('required_name');
                $data->filling_mark = $request->get('filling_mark');
                $data->header_name = $request->get('header_name');
                $data->created_by = 271;
                $data->updated_by = 271;
                $data->save();
                DB::commit();
            } catch(\Exception $e){
                return $e->getMessage();
            }
        }
    }

    public function jobRequiredUpdate(Request $request){
        // return $request;

        $required_name_exists = JobRequired::wherenotIn('id', [$request->get('id')])
            ->where('required_name', $request->get('required_name'))
            ->exists();
        $filling_mark_exists = JobRequired::whereNotIn('id', [$request->get('id')])
            ->where('filling_mark', $request->get('filling_mark'))
            ->exists();
        $header_name_exists = JobRequired::whereNotIn('id', [$request->get('id')])
            ->where('header_name', $request->get('header_name'))
            ->exists();
        if($required_name_exists){
            return 1;
        } else if($filling_mark_exists){
            return 2;
        } else if ($header_name_exists){
            return 3;
        } else {
            try{
                DB::beginTransaction();
                $data = JobRequired::find($request->get('id'));
                $data->seq = JobRequired::EditSeq($request->get('old_seq'), ($request->get('seq')));
                $data->required_name = $request->get('required_name');
                $data->filling_mark = $request->get('filling_mark');
                $data->header_name = $request->get('header_name');
                $data->created_by = 271;
                $data->updated_by = 271;
                $data->save();

                $data_sub = json_decode($request->sub_docs);
                $data_delete = json_decode($request->deleted_items);

                foreach($data_sub as $key => $value){
                    JobRequestSubDocument::updateOrInsert(
                        [ 'id' => $value->id],
                        [
                            'required_name' => $value->required_name,
                            'document_id' => $request->get('id'),
                            'filling_mark' => $value->filling_mark,
                            'header_name' => $value->header_name,
                            'active' => 1,
                        ]
                    );
                }

                if(count($data_delete)){
                    foreach($data_delete as $key => $value){
                        $jobRequestSubDocument = DB::table('job_request_sub_documents')->where('id', $value);
                        if ($jobRequestSubDocument) {
                            $jobRequestSubDocument->update(['active' => 0]);
                        }
                    }
                }

                DB::commit();
            } catch(\Exception $e){
                return $e->getMessage();
            }
        }
    }

    public function jobRequiredDelete(Request $request){
        // return $request;
        $ids = $request->id;

        try{
            JobRequired::whereIn('id', $ids)
            ->update([
                'deleted_by' => 271,
                'deleted_at' => now()
            ]);

            JobRequestRequirement::whereIn('document_id', $ids)
            ->update([
                'deleted_at' => now()
            ]);

            JobRequired::Reseq();
        }catch(\Exception $e){
            return $e->getMessage();
        }
        return response()->json(['success' => 'Delete Successfully ']);
    }

    public function getEmailRecipient(){

        $users = IconnUser::select('id', 'username');

        $data = EmailRecipient::joinSub($users, 'users', function($join){
            $join->on('email_recipients.user_id', '=', 'users.id');
        })
        ->select(
            'email_recipients.id',
            'email_recipients.user_id',
            'email_recipients.created_by',
            'email_recipients.created_at',
            'users.username',
            DB::raw("DATE_FORMAT(email_recipients.updated_at, '%Y-%m-%d %H:%i:%s') as updatedDate")
        )
        ->when(request('search'), function ($q) {
            for($i=0; $i<count(request('search')); $i++){
                $q->where(request('search')[$i]['column'], 'like', '%'.request('search')[$i]['val'].'%');
            }
            return $q;
        })
        ->when(request('sort') , function ($q) {
            for($i=0;$i<count(request('sort'));$i++){
                $q->orderBy(request('sort')[$i]['column'],request('sort')[$i]['val']);
            }
            return $q;
        })
        ->get();

        return $data;
    }
    
    public function insertJobRecipients(Request $request){
        $exist = EmailRecipient::where('user_id', $request->get('user_id'))->exists();
        if($exist){
            return 1;
        }
        try{
            DB::beginTransaction();
            $email_recipients = new EmailRecipient;
            $email_recipients->user_id = $request->user_id;
            $email_recipients->created_by = 271;
            $email_recipients->created_at = now();
            $email_recipients->updated_at = now();
            $email_recipients->save();
            DB::commit();
        }catch(\Exception $e){
            return $e->getMessage();
        }
        return $email_recipients;
    }

    public function deleteJobRecipients(Request $request){
        try{
            EmailRecipient::whereIn('id', $request->id)
            ->update([
                'deleted_by' => 271,
                'deleted_at' => now()
            ]);
        }catch(\Exeception $e){
            return $e->getMessage();
        }
    }
}
