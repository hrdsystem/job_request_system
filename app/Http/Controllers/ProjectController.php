<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;


class ProjectController extends Controller
{
    public function get_project_drawer(){
        // return 'test';
        $projects = Project::select(
            'projects.id',
            'projects.name as text',
            'project_branches.name'
        )
        ->when(request('keyword'), function ($q) {
            $q
            ->whereHas(
                'children', function ($query) {
                    $query
                        ->where('construction_code', 'like', '%'.request('keyword').'%')
                        ->orWhere('house_code', 'like', '%'.request('keyword').'%')
                        ->orWhere('shipping_flag', 'like', '%'.request('keyword').'%');
                }
            );
        })
        ->with([
            'children' => function ($query) {
                $query
                ->select(
                    'project_registered.project_id',
                    'project_registered.order',
                    'project_registered.id',
                    'project_registered.lot',
                    'project_registered.type_code',
                    'project_registered.old_type_code',
                    'project_registered.construction_code',
                    'project_registered.house_code',
                    'project_registered.shipping_flag',
                    'project_registered.marketing_id',
                    'project_designer_incharge.designer_id',
                    'project_registered_marketings.code as marketing_code',
                    'users.username',
                    'shipping_schedule_project_data.year',
                    'shipping_schedule_project_data.invoice'
                )
                ->leftJoin('project_designer_incharge','project_designer_incharge.project_id','project_registered.id')
                ->leftJoin('project_registered_marketings','project_registered_marketings.id','project_registered.marketing_id')
                ->leftJoin('users','users.id','project_designer_incharge.designer_id')
                ->leftJoin('shipping_schedule_project_data', function($join) {
                    $join
                        ->on('shipping_schedule_project_data.project_id', '=', 'project_registered.project_id')
                        ->on('shipping_schedule_project_data.order', '=', 'project_registered.order');
                })
                ->when(request('keyword'), function ($q) {
                    $q
                    ->where('construction_code', 'like', '%'.request('keyword').'%')
                    ->orWhere('house_code', 'like', '%'.request('keyword').'%')
                    ->orWhere('shipping_flag', 'like', '%'.request('keyword').'%');
                })
                ->when(request('path') && request('path') == 'cad_requests', function ($q) {
                    $q
                    ->addSelect('project_registered.category')
                    ->orderBy('project_registered.category','desc');
                })
                ->when(!request('path') || request('path') != 'cad_requests', function ($q) {
                    $q
                    ->where('project_registered.category', 0);
                })
                ->orderBy('project_registered.lot','asc')
                ->distinct();
            }
        ])
        ->leftJoin('project_branches','project_branches.id','projects.branch_id')
        ->orderBy('projects.seq','desc')
        ->get();
        $projects->each(function ($e) {
            $e->children->each(function ($value){
                if($value->order && $value->year && $value->invoice){
                    $value['iso_ship'] = "IQ" . str_pad($value->invoice, 2, 0, STR_PAD_LEFT) . "-" . substr($value->year, 2);
                    unset($value->year);
                    unset($value->invoice);
                    unset($value->order);
                    unset($value->shipping_flag);
                }
                else if (!$value->order || (!$value->year && !$value->invoice)) {
                    unset($value->year);
                    unset($value->invoice);
                    unset($value->days);
                    unset($value->order);
                }
            });
            $e->setAppends(['model']);
        });
        return $projects;
    }
}
