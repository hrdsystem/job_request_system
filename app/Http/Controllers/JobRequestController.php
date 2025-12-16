<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

use App\Mail\IconnMail;
use App\Models\JobRequestSubDocument;
use App\Models\JobRequired;
use App\Models\JobRequest;
use App\Models\IconnUser;
use App\Models\JobAttachment;
use App\Models\JobProjectList;
use App\Models\jobProjects;
use App\Models\JobRequestRequirement;
use App\Models\JobRequestUpload;
use App\Models\JobRequestUploadedFile;
use App\Models\Project;
use App\Models\ProjectRegistered;
use Illuminate\Support\Str;

use function PHPSTORM_META\map;

class JobRequestController extends Controller
{   

    private function base64Decode($file){
        $data = $file;
        list($type, $data) = explode(';', $data);
        list(, $data)      = explode(',', $data);
        return base64_decode($data);
    }
    
    public function filesystem(){
        if(App::environment('development') || App::environment('local') || App::environment('production')) {
            return 'public';
        }else{                                
            return 's3'; 
        }
    }

    private function file_details($attachment)
    {
        $attachment = (array) json_decode($attachment);

        if (!$attachment) {
            $file['orig_filename'] = null;
            $file['file_hash'] = null;
            $file['file_data'] = null;
        } else {
            // $unique_id = Uuid::generate();
            $unique_id = Str::uuid()->toString();
            $file = pathinfo($attachment['filename']);
            $file['orig_filename'] = $attachment['filename'];
            $file['file_hash'] = $unique_id . '.' . $file['extension'];
            $file['file_data'] = isset($attachment['data']) ? $attachment['data'] : null;
        }

        return $file;
    }

    public function openAttachment($attachment_id, $filename){

        // $attachment = JobAttachment::where('id', $attachment_id)->get();
        $attachment = DB::table('job_attachments')
        ->where('job_attachments.id', $attachment_id)
        ->first();
        $viewable = [
            'pdf',
            'jpg',
            'png'
        ];

        $extension = pathinfo($attachment->orig_filename, PATHINFO_EXTENSION);
        $inline = in_array(strtolower($extension), $viewable) ? 'inline' : 'attachment';
        $temp_filepath = tempnam(sys_get_temp_dir(), '');
        $file_data = Storage::disk($this->filesystem())->get('job_request/' . $attachment->job_request_id . '/attachments/' . $attachment->file_hash);
        
        file_put_contents($temp_filepath, $file_data);

        return response()
            ->download($temp_filepath, $attachment->orig_filename, ['filename' => $attachment->orig_filename], $inline)
            ->deleteFileAfterSend();
    }

    protected function recordViewDocument(JobRequestUpload $uploaded_file, int $userId) : void {
        $currentTime = now();
        
        DB::table('document_view_history')->insert([
            'request_id'  => $uploaded_file->request_id,
            'upload_id'   => $uploaded_file->document_id,
            'user_id'     => $userId,
            // 'user_id'     => Auth::id(),
            'viewed_at'   => $currentTime,
            'created_at'  => $currentTime, 
            'updated_at'  => $currentTime,
        ]);

        if (!isset($uploaded_file->viewed_by) || $uploaded_file->viewed_by != $userId) {
            DB::table('job_request_uploads')
                ->where('id', $uploaded_file->id)
                ->where('latest', true)
                ->update([
                    'viewed_by'   => $userId,
                    'date_viewed' => $currentTime
                ]);
        }
    }

    public function openDocument($reference, $filename){
        $ids = explode('-', $reference);

        $uploaded_file = JobRequestUpload::select(
            'job_request_uploads.*',
            'job_request_uploaded_files.orig_filename',
            'job_request_uploaded_files.file_hash'
        )
        ->join('job_request_uploaded_files', 'job_request_uploaded_files.upload_id', 'job_request_uploads.id')
        ->when(count($ids) === 2, function ($q) use ($ids){
            $q
            ->where('request_id', $ids[0])
            ->where('document_id', $ids[1])
            ->where('latest', true);
        })
        ->when(count($ids) === 1, function($q) use ($ids){
            $q
            ->where('job_request_uploaded_files.id', $ids[0]);
        })
        ->firstOrFail();

        if($uploaded_file){
            $viewable = [
                'pdf',
                'jpg',
                'png'
            ];
            

            $extension = pathinfo($uploaded_file->orig_filename, PATHINFO_EXTENSION);
            $inline = in_array(strtolower($extension), $viewable) ? 'inline' : 'attachment';
            $temp_filepath = tempnam(sys_get_temp_dir(), '');
            $file_data = Storage::disk($this->filesystem())->get('job_request/' . $uploaded_file->request_id . '/required_docs/' . $uploaded_file->file_hash);
            
            $tempo_id = 261;

            if ($tempo_id !== null){
                $this->recordViewDocument($uploaded_file, $tempo_id);
            }
            
            // $currentUserID = Auth::id();
            
            $requestor = JobRequest::where('id', $uploaded_file->request_id)->value('created_by');
            $temp_id = 261;
            if ($temp_id !== null && is_null($uploaded_file->viewed_by)) {
                DB::table('job_request_uploads')
                ->where('id', $uploaded_file->id)
                ->where('latest', true)
                ->update([
                    'viewed_by' => $temp_id,
                    'date_viewed' => now()
                ]);
            }
            
            file_put_contents($temp_filepath, $file_data);
            return response()
                ->download($temp_filepath, $filename . '-' . date('mdY') . '.' . $extension, ['filename' => $filename . '-' . date('mdY') . '.' . $extension], $inline)
                ->deleteFileAfterSend();
        }
    }

    public function getDocumentViewHistory(Request $request){
        // $users = IconnUser::select('id', 'username');

        // $history = viewDocumentHistory::joinSub($users, 'users', function($join){
        //     $join->on('document_view_history.user_id', '=', 'users.id');
        // })
        // ->select(
        //     'document_view_history.viewed_at',
        //     'users.username as viewer_name',
        //     'document_view_history.user_id'
        // )
        // ->where('upload_id', $request->input('upload_id'))
        // ->where('request_id', $request->input('request_id'))
        // ->get();

        $validated = $request->validate([
            'upload_id' => 'required|integer',
            'request_id' => 'required|integer',
        ]);
        
        $uploadId = $validated['upload_id'];
        $requestId = $validated['request_id'];

        $history = viewDocumentHistory::with(['viewer:id,username'])
            ->forDocument($uploadId, $requestId)
            ->get([
                'viewed_at',
                'user_id',
            ]);

        if ($history->isEmpty()) {
            return response()->json(['message' => 'No view history found.'], 404);
        }

        $transformedHistory = $history->map(function ($item) {
            return [
                'viewed_at' => $item->viewed_at,
                'viewed_user' => $item->viewer->username ?? null,
                'user_id' => $item->user_id,
            ];
        });
    
        return response()->json($transformedHistory);
    }

    public function upload_history(Request $request){
        $users = IconnUser::select('id', 'username');
    
        $history = JobRequestUpload::joinSub($users, 'users', function($join){
            $join->on('job_request_uploads.uploader', '=', 'users.id');
        })
        ->select(
            'job_request_uploads.*',
            'job_requireds.required_name',
            'job_requireds.filling_mark',
            'users.username as uploaded_by',
            'users2.username as viewed_by_user'
        )
        ->join('job_requireds', 'job_requireds.id', 'document_id')
        ->leftJoinSub($users, 'users2', function($join) {
            $join->on('users2.id', '=', 'job_request_uploads.viewed_by');
        })
        ->where('request_id', $request->input('request_id'))
        ->where('document_id', $request->input('document_id'))
        ->where('latest', 0)
        ->with('files')
        ->get();

        return $history;
    }

    // public function getJobRequests(Request $request){
    //     $jobRequest = JobRequest::with([
    //         'user: id, username, photo',
    //         'projectRegistration.project: id, name as projects_name',
    //         'attachments',
    //         'uploaaed_file',
    //         'requiredDocument'
    //     ])
    //     ->select('job_requests.*')
    //     // ->paginate()
    //     ->get();

    //     return $jobRequest;
    // }

    public function getJobRequests(Request $request){

        $users = IconnUser::select('id', 'username', 'photo');
        $project_registered = JobProjectList::select(
            'project_registered.id',
            'project_registered.construction_code',
            'project_registered.lot',
            'project_registered.project_id',
        );
        $projects = jobProjects::select('id', 'name');
    
        $jobRequestQuery = JobRequest::joinSub($users, 'users', function($join){
            $join->on('job_requests.created_by', '=', 'users.id');
        })
        ->joinSub($project_registered, 'project_registered', function($join){
            $join->on('job_requests.register_id', '=', 'project_registered.id');
        })
        ->joinSub($projects, 'projects', function($join){
            $join->on('projects.id', '=', 'project_registered.project_id');
        })
        ->select(
            'job_requests.id',
            'job_requests.register_id',
            'job_requests.project_name',
            'job_requests.subject',
            'job_requests.lot_number',
            'job_requests.status',
            'job_requests.requested_date',
            'job_requests.job_ecd',
            'job_requests.note',
            'job_requests.created_at',
            'users.username',
            'users.photo',
            'job_requests.updated_at',
            'project_registered.project_id',
            'project_registered.lot',
            'project_registered.construction_code',
            'projects.name as projects_name'
        )
        ->when(request('search'), function ($q) {
            for($i=0; $i<count(request('search')); $i++){
                $q->where(request('search')[$i]['column'], 'like', '%' . request('search')[$i]['val']. '%');
            }
            return $q;
        })

        ->when(request('sort') , function ($q) {
            for($i=0;$i<count(request('sort'));$i++){
                $q->orderBy(request('sort')[$i]['column'],request('sort')[$i]['val']);
            }
            return $q;
        })
        ->with(['attachments', 'uploaded_file'], 'requiredDocument');
        $jobRequests = $jobRequestQuery->get();
        $cnt = $jobRequests->count();
        
        return [$jobRequests, $cnt];
    }

    public function jobRequestInsert(Request $request){
        // return $request;

        $lot_number_exists = JobRequest::where('lot_number', $request->get('lot_number'))->exists();
        $subject_exists = JobRequest::where('subject', $request->get('subject'))->exists();
        if ($lot_number_exists){
            return 1;
        } else if ($subject_exists){
            return 2;
        } else{
            try{
                DB::beginTransaction();
                
                $data = new JobRequest;
                $data->register_id = $request->get('register_id');
                $data->project_name = $request->get('project_name');
                $data->subject = $request->get('subject');
                $data->lot_number = $request->get('lot_number');
                $data->status = 'NEW';
                $data->requested_date = $request->get('requested_date');
                $data->note = $request->get('note');
                $data->created_by = 261;
                $data->updated_by = 261;
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
    
                if (!empty($request->attachments)) {
                    foreach ($request->attachments as $file) {
                        $file_details = $this->file_details($file);
                        $attachments[] = [
                            'job_request_id' => $last_id,
                            'orig_filename' => $file_details['orig_filename'],
                            'file_hash' => $file_details['file_hash'],
                            'updated_by' => 211,
                            'updated_at' => new \DateTime
                        ];
        
                        if (!is_null($file_details['file_data'])) {
                            Storage::disk($this->filesystem())->put("job_request/" . $last_id . "/attachments/" . $file_details['file_hash'], $this->base64Decode($file_details['file_data']));
                        }
                    }
    
                    if (isset($attachments)) {
                        DB::table('job_attachments')->insert($attachments);
                    }
                }
    
                DB::commit();
            } catch(\Exception $e){
                return $e->getMessage();
            }
            return $data;
        }
    }

    public function jobRequestUpdate(Request $request){
        // return $request;

        $lot_number_exists = JobRequest::whereNotIn('id', [$request->get('id')])
            ->where('lot_number', $request->get('lot_number'))
            ->exists();
        $subject_exists = JobRequest::whereNotIn('id', [$request->get('id')])
            ->where('subject', $request->get('subject'))
            ->exists();
        if($lot_number_exists) {
            return 1;
        } else if ($subject_exists){
            return 2;
        } else{
            try{
                DB::beginTransaction();
                $data = JobRequest::find($request->get('id'));
                $data->register_id = $request->get('register_id');
                $data->project_name = $request->get('project_name');
                $data->subject = $request->get('subject');
                $data->lot_number = $request->get('lot_number');
                $data->requested_date = $request->get('requested_date');
                $data->note = $request->get('note');
                $data->created_by = 261;
                $data->updated_by = 261;
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
    
                
                $deleted_attachments = $request->input('deleted_attachments');
                if (!empty($deleted_attachments)) {
                    DB::table('job_attachments')
                        ->whereIn('id', $deleted_attachments)
                        ->update([
                            'deleted_by' => 211,
                            'deleted_at' => new \DateTime()
                        ]);
                }
    
                if (!empty($request->input('attachments'))) {
                    foreach ($request->input('attachments') as $item) {
                    $file = json_decode($item, true);
    
                    if (isset($file['data'])){
                        $file_details = $this->file_details($item);
                        $attachments[] = [
                            'job_request_id' => $data -> id,
                            'orig_filename' => $file_details['orig_filename'],
                            'file_hash' => $file_details['file_hash'],
                            'updated_by' => 211,
                            'updated_at' => new \DateTime
                        ];
        
                            if (!is_null($file_details['file_data'])) {
                                Storage::disk($this->filesystem())->put("job_request/" . $data->id . "/attachments/" . $file_details['file_hash'], $this->base64Decode($file_details['file_data']));
                            }
                        }
                    }
    
                    if (isset($attachments)) {
                        DB::table('job_attachments')->insert($attachments);
                    }
                }
    
                DB::commit();
                return response()->json(['Success' => 'Update Successful']);
            } catch(\Exception $e){
                return $e->getMessage();
            }
        }
    }

    public function jobRequestDelete(Request $request){
        try{
            JobRequest::whereIn('id', $request->id)
            ->update([
                'deleted_by' => 261,
                'deleted_at' => now()
            ]);
        }catch(\Exception $e){
            return $e->getMessage();
        }
        return response()->json(['success' => 'Delete Successfully ']);
    }

    public function jobRequestStatusChange(Request $request){
        try{
            DB::beginTransaction();
            $data = JobRequest::find($request->get('id'));
            $data->status = $request->get('status');
            $data->save();

            DB::commit();
        }catch(\Exception $e){
            return $e->getMessage();
        }
        return response()->json(['success' => 'Update Successfully ']);
    }

    public function jobRequestEcdChange(Request $request){
        try{
            DB::beginTransaction();
            $data = JobRequest::find($request->get('id'));
            $data->job_ecd = $request->get('ecd_date');
            $data->save();

            DB::commit();
        } catch(\Exception $e){
            return $e->getMessage();
        }
        return response()->json(['success' => 'Update Successfully ']);

    }

    public function getRequiredDocWithUpload($request_id)
    {
        $users = IconnUser::select('id', 'username', 'photo');
        
        $requirements = JobRequestRequirement::select(
            'job_request_requirements.id',
            'job_request_requirements.job_request_id',
            'job_request_requirements.document_id',
            'job_request_requirements.estimated_completion_date',
            'job_requireds.required_name',
            'job_requireds.filling_mark',
            'job_requireds.header_name',
            'job_request_uploads.id as upload_id',
            'job_request_uploads.uploader',
            'job_request_uploads.updating_reason',
            'job_request_uploads.viewed_by',
            'job_request_uploads.date_viewed',
            'job_request_uploads.date_uploaded',
            'job_requests.job_ecd',
            'users.username as job_uploader',
            'users2.username as viewed_by_user'
        )
        ->join('job_requireds', 'job_requireds.id', 'document_id')
        ->join('job_requests', 'job_requests.id', 'job_request_id')
        ->leftJoin('job_request_uploads', function($join) {
            $join
            ->on('job_request_uploads.request_id', '=', 'job_request_requirements.job_request_id')
            ->on('job_request_uploads.document_id', '=', 'job_request_requirements.document_id')
            ->where('job_request_uploads.latest', true);
        })
        ->leftJoinSub($users, 'users', function($join) {
            $join->on('users.id', '=', 'job_request_uploads.uploader');
        })
        ->leftJoinSub($users, 'users2', function($join) {
            $join->on('users2.id', '=', 'job_request_uploads.viewed_by');
        })
        ->whereNull('job_request_requirements.deleted_at')
        ->where('job_request_requirements.job_request_id', $request_id)
        ->get();

        $upload_ids = $requirements->pluck('upload_id')->toArray();
        $files = JobRequestUploadedFile::whereIn('upload_id', $upload_ids)->get();

        $data = $requirements->map(function ($obj) use ($files){

            $obj->uploads = $files->filter(function ($item) use ($obj){
                if ($item->upload_id == $obj->upload_id){
                    return true;
                }
            })->values();

            return $obj;
        });

        return $data;
    }

    public function getUploadedDocuments(Request $request){
        return DB::table('job_request_uploads')
            ->select(
                'job_request_uploads.id',
                'job_request_uploads.document_id',
                'job_request_uploaded_files.orig_filename',
                'job_request_uploaded_files.id as file_id',
            )
            ->join('job_requests', 'job_requests.id', 'job_request_uploads.request_id')
            ->join('job_request_uploaded_files', 'job_request_uploaded_files.upload_id', 'job_request_uploads.id')
            ->when(is_null($request->input('upload_id')), function ($q) use ($request) {
                $q
                ->where('job_request_uploads.request_id', $request->input('request_id'))
                ->where('job_request_uploads.document_id', $request->input('document_id'))
                ->where('job_request_uploads.latest', true);
            })
            ->when(!is_null($request->input('upload_id')), function ($q) use ($request) {
                $q
                ->where('job_request_uploads.id', $request->input('upload_id'));
            })
            ->get();
    }

    public function getRequiredDocuments(Request $request){
        return $this->getRequiredDocWithUpload($request->input('request_id'));
    }

    public function process_job_updates(Request $request){
        $request_id = $request->input('id');
        $register_id = $request->input('register_id');
        $lot_number = $request->input('lot_number');
        $updates = $request->input('updates');

        // $data['greetings'] = 'Good day!';
        // $data['header'] = 'New updates for the request' . $request->input('subject') . '.';
        // $data['project_name'] = $project->project_name;

        try {
            DB::beginTransaction();

            $haveNewECD = [];
            $maxEcdDate = null;
            $debug_output = [];
            
            foreach ($updates as $item){
                if ($item['changedECD']) {
                    $haveNewECD[] = $item['document_id'];
                    
                    if ($item['estimated_completion_date'] === null){
                        Log::info('CONDITION IS ON THE IF STATEMENT');
                        JobRequestRequirement::where('id', $item['id'])
                        ->update([
                            'estimated_completion_date' => now(),
                            'updated_at' => new \DateTime()
                        ]);
                    } else{
                        Log::info('CONDITION IS ON THE ELSE STATEMENT');
                        JobRequestRequirement::where('id', $item['id'])
                        ->update([
                            'estimated_completion_date' => $item['estimated_completion_date'],
                            'updated_at' => new \DateTime()
                        ]);
                    }
 
                    $currentEcd = new \DateTime($item['estimated_completion_date']);
                    if ($maxEcdDate === null || $currentEcd > $maxEcdDate){
                        $maxEcdDate = $currentEcd;
                    }
                }

                if (count($item['newUploads']) > 0){
                    $data['new_uploads'][] = $item['document_id'];
                    $upload_data = [
                        'request_id' => $request_id,
                        'document_id' => $item['document_id'],
                        'date_uploaded' => new \DateTime(),
                        'updating_reason' => $item['newUploadReasons'],
                        'send_date' => new \DateTime(),
                        'uploader' => 261,
                        'latest' => true
                    ];

                    DB::table('job_request_uploads')
                        ->where('request_id', $request_id)
                        ->where('document_id', $item['document_id'])
                        ->update(['latest' => false]);

                    DB::table('job_request_uploads')->insert($upload_data);

                    $upload_id = DB::getPdo()->lastInsertId();

                    if ($item['estimated_completion_date'] === null){
                        Log::info('CONDITION IS ON THE IF STATEMENT');
                        JobRequestRequirement::where('id', $item['id'])
                        ->update([
                            'estimated_completion_date' => $request->input('latest_ecd'),
                            'updated_at' => new \DateTime()
                        ]);
                    } else{
                        Log::info('CONDITION IS ON THE ELSE STATEMENT');
                        JobRequestRequirement::where('id', $item['id'])
                        ->update([
                            'estimated_completion_date' => $item['estimated_completion_date'],
                            'updated_at' => new \DateTime()
                        ]);
                    }

                    foreach ($item['newUploads'] as $upload){
                        $file_data = $this->file_details(json_encode($upload));

                        $files[] = [
                            'upload_id' => $upload_id,
                            'orig_filename' => $file_data['orig_filename'],
                            'file_hash' => $file_data['file_hash']
                        ];

                        if (!is_null($file_data['file_data'])) {
                            Storage::disk($this->filesystem())->put("job_request/" . $request_id . "/required_docs/" . $file_data['file_hash'], $this->base64Decode($file_data['file_data']));
                        }
                    }
                }
            }

            $jobRequestId = JobRequest::find($request_id);
            if (!$jobRequestId) {
                throw new \Exception("Job request with ID {$request_id} not found.");
            }
            $currentJobEcd = $jobRequestId->job_ecd ? new \DateTime($jobRequestId->job_ecd) : null;

            if ($maxEcdDate !== null && ($currentJobEcd === null || $maxEcdDate > $currentJobEcd)) {
                JobRequest::where('id', $request_id)
                    ->update([
                        'job_ecd' => $maxEcdDate->format('Y-m-d'),
                        'updated_by' => 261,
                        'updated_at' => new \DateTime()
                    ]);
            }

            if (isset($files) && count($files) > 0){
                DB::table('job_request_uploaded_files')->insert($files);
            }

            DB::commit();
            
            $rawToRecipients = $request->input('to_recipients');
            $rawCcRecipients = $request->input('cc_recipients');

            $to = $this->get_emails($rawToRecipients);
            $cc = $this->get_emails($rawCcRecipients);

            Log::info('Raw TO recipients from frontend:', ['to' => $rawToRecipients]);
            Log::info('Raw CC recipients from frontend:', ['cc' => $rawCcRecipients]);
            // dd($request->all(), $request->input('to_recipients'), $request->input('cc_recipients'));

            // $to = $this->get_emails(json_decode($request->input('to_recipients')));
            // $cc = $this->get_emails(json_decode($request->input('cc_recipients')));
            Log::info('Processed TO recipients:', $to);
            Log::info('Processed CC recipients:', $cc);
            if (empty($to)) {
                Log::error('Mail Error: "To" recipient list is empty after get_emails.');
                // It's crucial to return an error or handle this gracefully.
                // Otherwise, Laravel will throw the "An email must have a "To", "Cc", or "Bcc" header" error.
                return response()->json(['message' => 'No valid "To" recipients found. Email not sent.'], 400);
            }
            $subject = ' [JOB Request - New Job Updates] ' . $request->input('subject') . '.';
            // $project = JobRequest::select(
            //     'job_requests.project_name'
            // )
            $projects = Project::select('id', 'name');

            $project = ProjectRegistered::joinSub($projects, 'projects', function($join) {
                $join->on('project_registered.project_id', '=', 'projects.id');
            })
            ->select(
                'project_registered.construction_code',
                'projects.name',
            )
            ->where('project_registered.id', $register_id)
            ->firstOrFail();
            $data = [
                'greetings' => 'Good day!',
                'header' => 'New updates for the request ' . $request->input('subject') . '.',
                'project_name' => $project->name,
                'subject' => $project->construction_code,
                'lot_number' => $lot_number,
                'sender_username' => 'Test User'
            ];
            
            $data['haveNewECD'] = $haveNewECD;
            $data['documents_uploaded'] = $this->getRequiredDocWithUpload($request_id);

            if (count($cc) > 0){
                Log::info('Sending email with TO and CC recipients.');
                Mail::to($to)
                    ->cc($cc)
                    ->send(new IconnMail($data, $subject, 'email.job_request_hrd_updates_email'));
            } else{
                Log::info('Sending email with only TO recipients (CC list is empty).');
                Mail::to($to)->send(new IconnMail($data, $subject, 'email.job_request_hrd_updates_email'));
            }

            Log::info('Email sent successfully for request_id: ' . $request_id);

            

            return ['request_id' => $request_id];
            return response()->json(['debug', $debug_output]);
        } catch(\Exception $e){
            return $e->getMessage();
        }
    }

    public function process_job_updates2(Request $request){
        $request_id = $request->input('id');
        $register_id = $request->input('register_id');
        $lot_number = $request->input('lot_number');
        $updates = (array) $request->input('updates', []);

        $files = [];
        $haveNewECD = [];
        $maxEcdDate = null;

        try {
            DB::beginTransaction();

            $results = $this->processRequirementsAndUploads(
                $request_id,
                $updates,
                $request->input('latest_ecd')
            );

            $haveNewECD = $results['haveNewECD'];
            $maxEcdDate = $results['maxEcdDate'];
            $files = $results['files'];
            
            $this->updateJobMasterEcd($request_id, $maxEcdDate);

            if (!empty($files)) {
                DB::table('job_request_uploaded_files')->insert($files);
            }

            DB::commit();

            $this->sendUpdateNotification(
                $request,
                $register_id,
                $lot_number,
                $request_id,
                $haveNewECD
            );

            Log::info('Job update and email sent successfully for request_id: ' . $request_id);

            return response()->json(['request_id' => $request_id], 200);
        } catch(Exception $e){

        }
    }
    
    private function processRequirementsAndUploads($request_id, array $updates, $latest_ecd): array{
        if(empty($updates)){
            return [
                'haveNewECD' => [],
                'maxEcdDate => null',
                'files' => []
            ];
        }
        
        $haveNewECD = [];
        $maxEcdDate = null;
        $files = [];
        $updates = (array) $updates;


        foreach ($updates as $item){
            $itemId = $item['id'];
            $docId = $item['document_id'];

            if ($item['changedECD']) {
                $ecdToUse = $item['estimated_completion_date'] ?? now();

                JobRequestRequirement::where('id', $itemId)->update([
                    'estimated_completion_date' => $ecdToUse,   
                    'updated_at' => now(),
                ]);

                $haveNewECD[] = $docId;

                $currentEcd = new \DateTime((string)$ecdToUse);
                if ($maxEcdDate === null || $currentEcd > $maxEcdDate) {
                    $maxEcdDate = $currentEcd;
                }
            }

            $newUploads = (array) ($item['newUploads'] ?? []);

            if (count($newUploads) > 0) {
                $ecdToUseUpload = $item['estimated_completion_date'] ?? $latest_ecd;

                JobRequestRequirement::where('id', $itemId)->update([
                    'estimated_completion_date' => $ecdToUseUpload,
                    'updated_at' => now()
                ]);

                DB::table('job_request_uploads')
                    ->where('request_id', $request_id)
                    ->where('document_id', $docId)
                    ->update(['latest' => false]);
    
                DB::table('job_request_uploads')->Insert([
                    'request_id' => $request_id,
                    'document_id' => $docId,
                    'date_uploaded' => now(),
                    'updating_reason' => $item['newUploadReasons'],
                    'send_date' => now(),
                    'uploader' => 261,
                    'latest' => true,
                ]);
                $upload_id = DB::getPdo()->lastInsertId(); 
    
                foreach ($newUploads as $upload) {
                    $file_data = $this->file_details(json_encode($upload));
    
                    $files[] = [
                        'upload_id' => $upload_id,
                        'orig_filename' => $file_data['orig_filename'],
                        'file_hash' => $file_data['file_hash']
                    ];
    
                    if (!is_null($file_data['file_data'])) {
                        Storage::disk($this->filesystem())->put(
                            "job_request/{$request_id}/required_docs/{$file_data['file_hash']}", 
                            $this->base64Decode($file_data['file_data'])
                        );
                    }
                }
            }
        }

        return [
            'haveNewECD' => $haveNewECD,
            'maxEcdDate' => $maxEcdDate,
            'files' => $files
        ];
    }

    private function updateJobMasterEcd($request_id, $maxEcdDate) {

        $jobRequest = JobRequest::find($request_id);

        if (!$jobRequest){
            throw new \Exception("Job request with ID {$request_id} not found.");
        }

        $currentJobEcd = $jobRequest->job_ecd ? new \DateTime($jobRequest->job_ecd) : null;
        
        if ($maxEcdDate !== null && ($currentJobEcd === null || $maxEcdDate > $currentJobEcd)) {
            JobRequest::where('id', $request_id)
                ->update([
                    'job_ecd' => $maxEcdDate->format('Y-m-d'),
                    'updated_by' => 261,
                    // 'updated_by' => Auth::id(),
                    'updated_at' => now()
                ]);
        }
    }

    private function sendUpdateNotification(Request $request, $register_id, $lot_number, $request_id,array $haveNewECD)
    {
        $projects = Project::select('id', 'name');
        $project = JobProjectList::joinSub($projects, 'projects', function($join) {
            $join->on('project_registered.project_id', '=', 'projects.id');
        })
        ->select('project_registered.construction_code', 'projects.name')
        ->where('project_registered.id', $register_id)
        ->firstOrFail();

        $toIds = Arr::flatten($request->input('to_recipients'));
        $ccIds = Arr::flatten($request->input('cc_recipients'));

        $to = $this->get_email_details($toIds);
        $cc = $this->get_email_details($ccIds);

        if (empty($to)) {
            Log::warning('Email skip: No valid "To" recipients found.');
            return; 
        }

        $subject = ' [Job Request - New Job Updates] ' . $request->input('subject');

        $data = [
            'greetings' => 'Good Day!',
            'header' => 'New Updates for the request '. $request->input('subject'),
            'project_name' => $project->name,
            'subject' => $project->construction_code,
            'lot_number' => $lot_number,
            'sender_username' => 'Test User',
            // 'sender_username' => Auth::id(),
            'haveNewECD' => $haveNewECD,
            'documents_uploaded' => $this->getRequiredDocWithUpload($request_id)
        ];

        $mailable = new IconnMail($data, $subject, 'email.job_request_hrd_updates_email');
        $mailer = Mail::to($to);

        if (count($cc) > 0) {
            $mailer->cc($cc);
            Log::info('Sending email with TO and CC recipients.');
        } else{
            Log::info('Sending email with only TO recipients (CC list is empty).');
        }

        $mailer->send($mailable);
    }

    public function get_projects(){
        try{
            $data = jobProjects::select(
                '*'
            )
            ->get();

            return $data;
        } catch(\Exeception $e){
            return $e->getMessage();
        }
    }

    public function get_project_list(){
        try{
            $data = JobProjectList::select(
                'project_registered.id as project_registered_id',
                'project_registered.construction_code',
                'project_registered.lot',
                'project_registered.project_id',
                'projects.id as projects_id',
                'projects.name as project_name'
            )
            ->leftJoin('projects', 'projects.id', 'project_registered.project_id')
            ->get()
            ->unique('project_name');

            return response()->json($data->values());
        } catch(\Exeception $e){
            return $e->getMessage();
        }
    }

    public function get_lots_for_project($project_id){
        try{
            $data = JobProjectList::select(
                'project_registered.id as project_registered_id',
                'project_registered.lot',
                'project_registered.construction_code',
                'projects.name as project_name'
            )
            ->leftJoin('projects', 'projects.id', 'project_registered.project_id')
            ->where('project_registered.project_id', $project_id)
            ->get()
            ->unique('lot')
            ->values();

            return response()->json($data);
        } catch(\Exception $e){
            return $e->getMessage();
        }
    }

    public function masterUsers(){
        try{
            $data = IconnUser::select(
                'users.id',
                'users.username',
                'users.photo',
                'users.email'
            )
            ->whereNotNull('users.photo')
            ->orderBy('seq', 'desc')
            ->get();
            
            return $data;
        } catch(\Exeception $e){
            return $e->getMessage();
        }
    }

    public function get_email_details($ids){
        return IconnUser::select('users.email', 'users.username as name')
            ->whereIn('users.id', $ids)
            ->whereNotNull('users.email')
            ->get()->toArray();
    }
}
