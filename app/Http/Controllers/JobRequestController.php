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
            $requestor = JobRequest::where('id', $uploaded_file->request_id)->value('created_by');

            $testUserID = 277;
            if ($testUserID === $requestor && is_null($uploaded_file->viewed_by)) {
                DB::table('job_request_uploads')
                    ->where('id', $uploaded_file->id)
                    ->where('latest', true)
                    ->update([
                        'viewwed_by' => $testUserID,
                        'viewed_date' => now()
                    ]);
            }

            file_put_contents($temp_filepath, $file_data);
            return response()
                ->download($temp_filepath, $filename . '-' . date('mdY') . '.' . $extension, ['filename' => $filename . '-' . date('mdY') . '.' . $extension], $inline)
                ->deleteFileAfterSend();
        }
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
            'users.username as uploaded_by'
        )
        ->join('job_requireds', 'job_requireds.id', 'document_id')
        ->where('request_id', $request->input('request_id'))
        ->where('document_id', $request->input('document_id'))
        ->where('latest', 0)
        ->with('files')
        ->get();

        return $history;
    }

    public function getJobRequests(Request $request){

        // $cnt = JobRequest::when(request('search'), function ($q) {
        //     for($i=0;$i<count(request('search'));$i++){
        //         return $q->orWhere(request('search')[$i]['column'],'like','%'.request('search')[$i]['val'].'%');
        //     }
        // })->count();

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
            for($i=0;$i<count(request('search'));$i++){
                $searchColumn = request('search')[$i]['column'];
                $searchValue = request('search')[$i]['val'];

                return $q->orWhere($searchColumn, 'like', '%' . $searchValue . '%');
            }
        })
        ->when(request('sort') , function ($q) {
            for($i=0;$i<count(request('sort'));$i++){
                $q->orderBy(request('sort')[$i]['column'],request('sort')[$i]['val']);
            }
            return $q;
        })
        ->with(['attachments', 'uploaded_file'], 'requiredDocument')
        ->get();
        $cnt = $jobRequestQuery->count();


        // $jobRequestIds = $data->pluck('id');

        // $uploaded = DB::table('job_request_uploaded_files')
        //     ->select(
        //         'job_request_uploads.request_id',
        //         'job_request_uploads.document_id',
        //     )
        //     ->leftJoin('job_request_uploads', 'job_request_uploads.id', 'upload_id')
        //     ->leftJoin('job_requests', 'job_requests.id', 'job_request_uploads.request_id')
        //     ->when(!request('showLatest'), function($q) use ($jobRequestIds){
        //         $q->whereIn('job_request_uploads.id', $jobRequestIds);
        //     })
        //     ->when(request('showLatest'), function($q) use ($previousRequestIds){
        //         $q->whereIn('job_request_uploads.request_id', $previousRequestIds);
        //     })
        //     ->get();

        // $data->each(function ($jobRequest){
        //     $uploadRequestIds = $jobRequest->uploaded_file->pluck('request_id')->toArray();
        //     $jobRequestIds = $jobRequest->requiredDocument->pluck('job_request_id')->toArray();

        //     JobRequest::latest_upload($jobRequest, $jobRequestIds, $uploadRequestIds);
        // });

        // $filteredJobQuery->each(function ($jobRequest) {
        //     $requiredDocIds = $jobRequest->requiredDocument->pluck('document_id');
            
        //     $latestUploads = collect();
        //     foreach ($requiredDocIds as $docId){
        //         $latestUpload = $jobRequest
        //     }
        // })
        
        return [$jobRequestQuery, $cnt];
    }

    public function jobRequestInsert(Request $request){
        // return $request;

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

    public function jobRequestUpdate(Request $request){
        // return $request;
        try{
            DB::beginTransaction();
            $data = JobRequest::find($request->get('id'));
            $data->register_id = $request->get('register_id');
            $data->project_name = $request->get('project_name');
            $data->subject = $request->get('subject');
            $data->lot_number = $request->get('lot_number');
            $data->requested_date = $request->get('requested_date');
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

    public function jobRequestDelete(Request $request){
        try{
            JobRequest::whereIn('id', $request->id)
            ->update([
                'deleted_by' => 271,
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
            'users.username as job_uploader'
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
        $updates = $request->input('updates');

        // $data['greetings'] = 'Good day!';
        // $data['header'] = 'New updates for the request' . $request->input('subject') . '.';
        // $data['project_name'] = $project->project_name;

        try {
            DB::beginTransaction();

            JobRequest::where('id', $request_id)
                ->update([
                    'job_ecd' => $request->input('latest_ecd'),
                    'updated_by' => 271,
                    'updated_at' => new \DateTime()
                ]);

            $haveNewECD = [];
            
            foreach ($updates as $item){
                if ($item['changedECD']) {
                    $haveNewECD[] = $item['document_id'];
                    
                    JobRequestRequirement::where('id', $item['id'])
                    ->update([
                        'estimated_completion_date' => $item['estimated_completion_date'],
                        'updated_at' => new \DateTime()
                    ]);
                }

                if (count($item['newUploads']) > 0){
                    $data['new_uploads'][] = $item['document_id'];
                    $upload_data = [
                        'request_id' => $request_id,
                        'document_id' => $item['document_id'],
                        'date_uploaded' => new \DateTime(),
                        'updating_reason' => $item['newUploadReasons'],
                        'send_date' => new \DateTime(),
                        'uploader' => 271,
                        'latest' => true
                    ];

                    DB::table('job_request_uploads')
                        ->where('request_id', $request_id)
                        ->where('document_id', $item['document_id'])
                        ->update(['latest' => false]);

                    DB::table('job_request_uploads')->insert($upload_data);

                    $upload_id = DB::getPdo()->lastInsertId();

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
            $project = JobRequest::select(
                'job_requests.project_name'
            )
            ->where('id', $request_id)
            ->firstOrFail();
            $data = [
                'greetings' => 'Good day!',
                'header' => 'New updates for the request ' . $request->input('subject') . '.',
                'project_name' => $project->project_name,
                'sender_username' => 'Test User'
            ];
            
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
        } catch(\Exception $e){
            return $e->getMessage();
        }
    }

    private function get_emails($users)
    {
        // Log the input to get_emails
        Log::info('get_emails - Input $users:', ['users_array' => $users]);

        if (!is_array($users) || empty($users)) {
            Log::warning('get_emails - $users is not a valid array or is empty, returning empty result.');
            return [];
        }

        $queryEmail = IconnUser::select('users.email', 'users.username as name')
            ->whereIn('users.id', $users)
            ->whereNotNull('users.email')
            ->get();
        
        // Log the raw result from the database before converting to array
        Log::info('get_emails - Query Result (Collection):', ['result' => $queryEmail->toArray()]);

        $toArrayEmails = $queryEmail->toArray();

        // Log the result after toArray()
        Log::info('get_emails - After to Array():', ['array_result' => $toArrayEmails]);

        $filteredEmails = array_filter($toArrayEmails);

        // Log the results after array_filter()
        Log::info('get_emails - After array_filter():', ['filtered_emails' => $filteredEmails]);

        $finalResults = array_values($filteredEmails);

        // Log the final result before returning

        return $finalResults;

        // return array_values(array_filter(IconnUser::select('email', 'username as name')
        //     ->whereIn('id', $users)
        //     ->whereNotNull('email')
        //     ->get()
        //     ->toArray()
        // ));
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

    // public function filterProjects(Request $request){

    //     $project = JobProjectList::select(
    //         'project_registered.id as project_registered_id',
    //         'project_registered.construction_code',
    //         'project_registered.lot',
    //         'project_registered.project_id',
    //         'projects.id as projects_id',
    //         'projects.name as project_name'
    //     )
    //     ->leftJoin('projects', 'projects.id', 'project_registered.project_id');

    //     if ($request->filled('project_name')) {
    //         $project->where('project_name', $request->test_pname);

    //         if ($request->filled('lot')) {
    //             $subjects = $project->where('project_registered.lot', $request->test_lot)
    //                 ->distinct()
    //                 ->pluck('construction_code');
    //             return response()->json($subjects);
    //         }

    //         $lots = $project->distinct()->pluck('lot');
    //             return response()->json($lots);
    //     }

    //     $projects = $project->distinct()->pluck('project_name');
    //         return response()->json($projects);
    // }

    public function masterUsers(){
        try{
            $data = IconnUser::select(
                'users.id',
                'users.username',
                'users.photo',
                'users.email'
            )
            ->orderBy('seq', 'desc')
            ->get();
            
            return $data;
        } catch(\Exeception $e){
            return $e->getMessage();
        }
    }
}
