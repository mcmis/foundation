<?php

namespace MCMIS\Foundation\Base\Complain;


use MCMIS\Foundation\Traits\Complain\DocumentTrait;
use MCMIS\Foundation\Traits\Complain\ListingTrait;
use MCMIS\Foundation\Traits\Complain\LocationTrait;
use MCMIS\Foundation\Traits\Complain\PhotoTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use MCMIS\Foundation\Traits\Complain\ExportComplaintsExcel;
use Illuminate\Support\Facades\DB;
use Auth;
use MCMIS\Foundation\BaseController;

class Controller extends BaseController
{

    use DocumentTrait, PhotoTrait, ListingTrait, LocationTrait, ExportComplaintsExcel;

    protected $workflow;

    public function categoryOptionFields($default = false){
        $items = app('model.category.option')->where('complain_category_id', '=', $default)->get();

        return view('acciones.complain.partial.form.category_options', [
            'options' => $items,
        ]);
    }

    /**
     * @param Request $keyword
     * @return mixed
     */
    public function search(Request $keyword){
        $item = app('model.complain')->where('complain_no', '=', $keyword->input('complain_no'));
        if(Auth::user()->hasRole('fieldworker'))
            $item = $item->leftJoin('complaint_assignments', 'complaint_assignments.complaint_id', '=', 'complaint.id')
                ->whereIn('complaint_assignments.employee_id', (count($employees = Auth::user()->employee->lists('id')->toArray()) ? $employees : [0]));
        elseif(Auth::user()->hasRole('supervisor'))
            $item = $item->leftJoin('complaint_assignments', 'complaint_assignments.complaint_id', '=', 'complaint.id')
                ->whereIn('complaint_assignments.department_id', Auth::user()->departments()->lists('id'));
        elseif(Auth::user()->hasRole('reader'))
            $item = $item->where('complaint.user_id', '=', Auth::user()->id);

        $item = $item->first();

        if(!$item){
            return redirect()->back()->withErrors('Complain #' . $keyword->input('complain_no') . ' not found', 'complain')->withInput();
        }
        return redirect()->route('complain', $item->complain_no);
    }

    /**
     * @param $complaint
     * @param $id
     * @return mixed
     */
    public function updateStatus($complaint, $id){
        $complain = app('model.complain')->where('complain_no', $complaint)
            ->update(['status' => $id]);

        if($complain)
            flash()->success(trans('alert.complain.status.updated', ['complain_no' => $complaint]));
        else
            flash()->error(trans('alert.complain.status.update.fail', ['complain_no' => $complaint]));

        return redirect()->route('complain', $complaint);
    }

    public function complaintCounter(){
        return app('model.status')->withCount(['complaints' => function($query){
            $query->where('seen', '=', false);
        }])->get()->toArray();
    }

    public function complaintsUnassignedCounter(){
        return app('model.unassignment')->count();
    }

    public function seen($complain_no){
        return app('model.complain')->where('complain_no', '=', $complain_no)->update(['seen' => true]);
    }

    public function timeline($complaint){
        //->whereColumn('status', '<>', 'last_status')
        $comments = $complaint->comments()
            ->leftJoin('complain_status', function($join){
                $join->on('complaint_comments.status', '=', 'complain_status.id');
            })->leftJoin('users', function($join){
                $join->on('complaint_comments.user_id', '=', 'users.id');
            })->select(DB::raw('concat(users.name, if(exists(select 1 from employee_user where employee_user.user_id = complaint_comments.user_id), " (Staff)", ""))'),
                'complain_status.title',
                DB::raw('min(complaint_comments.created_at) as started'),
                DB::raw('max(complaint_comments.updated_at) as ended'))
            ->groupBy('complaint_comments.user_id', 'complaint_comments.status')->get()->toArray();

        $dataTable = \Lava::DataTable();

        $dataTable->addStringColumn('Name');
        $dataTable->addStringColumn('Status');
        $dataTable->addDateColumn('Start');
        $dataTable->addDateColumn('End');

        $data = array_map(function($value){
            $value['started'] = Carbon::parse($value['started']);
            $value['ended'] = Carbon::parse($value['ended']);
            return array_values($value);
        }, $comments);

        if($data) $dataTable->addRows(array_values($data));

        \Lava::TimelineChart('Timeline', $dataTable, [
            'title' => 'Classes',
            'width' => '100%',
            'height' => '90%',
            'timeline' => [
                'colorByRowLabel' => false
            ],
            'isStacked' => true,
        ]);

        return $comments;
    }

}
