<?php
namespace MCMIS\Foundation\Traits\Complain;


use App\ComplainCategories;
use App\ComplainSources;
use App\Complaint;
use App\ComplaintLocation;
use App\Http\Controllers\Reports\GraphController;
use App\Models\Organization\Employee;
use App\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

trait ListingTrait
{

    public function index(Request $request, $pagination = true, $export = false){
        if(Auth::user()->hasRole('operator')){
            $items = Complaint::orderBy('complaint.updated_at', 'desc');
        }elseif(Auth::user()->hasRole('supervisor')){
            $items = Complaint::whereHas('assignments', function ($q){
                $q->whereIn('department_id', Auth::user()->departments()->lists('id'));
            })->orderBy('complaint.updated_at', 'desc');

        }elseif(Auth::user()->hasRole('fieldworker')){
            $items = Complaint::leftJoin('complaint_assignments', function($join){
                $join->on('complaint_assignments.complaint_id', '=', 'complaint.id');
            })->orderBy('complaint.updated_at', 'desc')
                ->whereIn('complaint_assignments.employee_id', Auth::user()->employee->lists('id'))
                ->groupBy('complaint_assignments.complaint_id');
        }else
            $items = Complaint::where('user_id', '=', DB::raw(Auth::user()->id))
                ->latestFirst()->statusReceived();

        /* Add Filters to Model */
        $items = $this->queryFilter($items, $request);
        /* End Filters to Model */

        if(Auth::user()->hasRole('operator') || Auth::user()->hasRole('supervisor')  || Auth::user()->hasRole('fieldworker')) $items = $items->doesntHave('parent');

        if($export) return $items;

        if($pagination) $items = $items->paginate(config('csys.settings.pagination.complaints'));
        else $items = $items->get();

        return view('acciones.complain.list', array_merge([
            'items' => $items->appends($request->except('page')),
            'complaints_counter' => $this->complaintCounter(),
            'complaints_unassigned_counter' => $this->complaintsUnassignedCounter(),
            'export_link' => ['query' => $request->getQueryString(), 'params' => [], 'request' => 'index'],
        ], $this->filters($request)));
    }

    public function verifiedIndex(Request $request, $department = null, $pagination = true, $export = false){
        if(Auth::user()->hasRole('admin') || Auth::user()->hasRole('operator'))
            $items = Complaint::latestFirst()->statusVerified();
        elseif(Auth::user()->hasRole('supervisor')){
            $items = Complaint::leftJoin('complaint_assignments', function($join){
                $join->on('complaint_assignments.complaint_id', '=', 'complaint.id');
            })->latestFirst()->statusVerified()
                ->whereIn('complaint_assignments.department_id', ($department ? [$department] : Auth::user()->departments()->lists('id')))
                ->groupBy('complaint_assignments.complaint_id');

        }elseif(Auth::user()->hasRole('fieldworker'))
            $items = Complaint::rightJoin('complaint_assignments', function($join){
                $join->on('complaint_assignments.complaint_id', '=', 'complaint.id');
            })->latestFirst()->statusVerified()
                ->whereIn('complaint_assignments.employee_id', Auth::user()->employee->lists('id'))
                ->groupBy('complaint_assignments.complaint_id');
        else
            $items = Complaint::where('user_id', '=', DB::raw(Auth::user()->id))
                ->latestFirst()->statusVerified();

        /* Add Filters to Model */
        $items = $this->queryFilter($items, $request);
        /* End Filters to Model */

        if(Auth::user()->hasRole('operator') || Auth::user()->hasRole('supervisor')  || Auth::user()->hasRole('fieldworker')) $items = $items->doesntHave('parent');

        if($export) return $items;

        if($pagination) $items = $items->paginate(config('csys.settings.pagination.complaints'));
        else $items = $items->get();

        return view('acciones.complain.list', array_merge([
            'items' => $items->appends($request->except('page')),
            'complaints_counter' => $this->complaintCounter(),
            'complaints_unassigned_counter' => $this->complaintsUnassignedCounter(),
            'export_link' => ['query' => $request->getQueryString(), 'params' => [
                'department' => $department,
            ], 'request' => 'verifiedIndex'],
        ], $this->filters($request)));
    }

    public function processIndex(Request $request, $department = null, $pagination = true, $export = false){
        if(Auth::user()->hasRole('admin') || Auth::user()->hasRole('operator'))
            $items = Complaint::latestFirst()->statusInprocess();
        elseif(Auth::user()->hasRole('supervisor')){
            $items = Complaint::leftJoin('complaint_assignments', function($join){
                $join->on('complaint_assignments.complaint_id', '=', 'complaint.id');
            })->latestFirst()->statusInprocess()
                ->whereIn('complaint_assignments.department_id', ($department ? [$department] : Auth::user()->departments()->lists('id')))
                ->groupBy('complaint_assignments.complaint_id');

        }elseif(Auth::user()->hasRole('fieldworker'))
            /* Fieldworker query

        $items = Complaint::whereHas('assignments', function($q){
                $q->whereIn('employee_id', Auth::user()->employee->lists('id'));
            })->orderBy('complaint.updated_at', 'desc');

        */
            $items = Complaint::rightJoin('complaint_assignments', function($join){
                $join->on('complaint_assignments.complaint_id', '=', 'complaint.id');
            })->latestFirst()->statusInprocess()
                ->whereIn('complaint_assignments.employee_id', Auth::user()->employee->lists('id'))
                ->groupBy('complaint_assignments.complaint_id');
        else
            $items = Complaint::where('user_id', '=', DB::raw(Auth::user()->id))
                ->latestFirst()->statusInprocess();

        /* Add Filters to Model */
        $items = $this->queryFilter($items, $request);
        /* End Filters to Model */

        if(Auth::user()->hasRole('operator') || Auth::user()->hasRole('supervisor')  || Auth::user()->hasRole('fieldworker')) $items = $items->doesntHave('parent');

        if($export) return $items;

        if($pagination) $items = $items->paginate(config('csys.settings.pagination.complaints'));
        else $items = $items->get();

        return view('acciones.complain.list', array_merge([
            'items' => $items->appends($request->except('page')),
            'complaints_counter' => $this->complaintCounter(),
            'complaints_unassigned_counter' => $this->complaintsUnassignedCounter(),
            'export_link' => ['query' => $request->getQueryString(), 'params' => [
                'department' => $department,
            ], 'request' => 'processIndex'],
        ], $this->filters($request)));
    }

    public function discardIndex(Request $request, $department = null, $pagination = true, $export = false){
        if(Auth::user()->hasRole('admin') || Auth::user()->hasRole('operator'))
            $items = Complaint::latestFirst()->statusDiscard();
        elseif(Auth::user()->hasRole('supervisor')){
            $items = Complaint::leftJoin('complaint_assignments', function($join){
                $join->on('complaint_assignments.complaint_id', '=', 'complaint.id');
            })->latestFirst()->statusDiscard()
                ->whereIn('complaint_assignments.department_id', ($department ? [$department] : Auth::user()->departments()->lists('id')))
                ->groupBy('complaint_assignments.complaint_id');

        }elseif(Auth::user()->hasRole('fieldworker'))
            $items = Complaint::rightJoin('complaint_assignments', function($join){
                $join->on('complaint_assignments.complaint_id', '=', 'complaint.id');
            })->latestFirst()->statusDiscard()
                ->whereIn('complaint_assignments.employee_id', Auth::user()->employee->lists('id'))
                ->groupBy('complaint_assignments.complaint_id');
        else
            $items = Complaint::where('user_id', '=', DB::raw(Auth::user()->id))
                ->latestFirst()->statusDiscard();

        /* Add Filters to Model */
        $items = $this->queryFilter($items, $request);
        /* End Filters to Model */

        if(Auth::user()->hasRole('operator') || Auth::user()->hasRole('supervisor')  || Auth::user()->hasRole('fieldworker')) $items = $items->doesntHave('parent');

        if($export) return $items;

        if($pagination) $items = $items->paginate(config('csys.settings.pagination.complaints'));
        else $items = $items->get();

        return view('acciones.complain.list', array_merge([
            'items' => $items->appends($request->except('page')),
            'complaints_counter' => $this->complaintCounter(),
            'complaints_unassigned_counter' => $this->complaintsUnassignedCounter(),
            'export_link' => ['query' => $request->getQueryString(), 'params' => [
                'department' => $department,
            ], 'request' => 'discardIndex'],
        ], $this->filters($request)));
    }

    public function failedIndex(Request $request, $department = null, $pagination = true, $export = false){
        if(Auth::user()->hasRole('admin') || Auth::user()->hasRole('operator'))
            $items = Complaint::latestFirst()->statusFailed();
        elseif(Auth::user()->hasRole('supervisor')){
            $items = Complaint::leftJoin('complaint_assignments', function($join){
                $join->on('complaint_assignments.complaint_id', '=', 'complaint.id');
            })->latestFirst()->statusFailed()
                ->whereIn('complaint_assignments.department_id', ($department ? [$department] : Auth::user()->departments()->lists('id')))
                ->groupBy('complaint_assignments.complaint_id');

        }elseif(Auth::user()->hasRole('fieldworker'))
            $items = Complaint::rightJoin('complaint_assignments', function($join){
                $join->on('complaint_assignments.complaint_id', '=', 'complaint.id');
            })->latestFirst()->statusFailed()
                ->whereIn('complaint_assignments.employee_id', Auth::user()->employee->lists('id'))
                ->groupBy('complaint_assignments.complaint_id');
        else
            $items = Complaint::where('user_id', '=', DB::raw(Auth::user()->id))
                ->latestFirst()->statusFailed();

        /* Add Filters to Model */
        $items = $this->queryFilter($items, $request);
        /* End Filters to Model */

        if(Auth::user()->hasRole('operator') || Auth::user()->hasRole('supervisor')  || Auth::user()->hasRole('fieldworker')) $items = $items->doesntHave('parent');

        if($export) return $items;

        if($pagination) $items = $items->paginate(config('csys.settings.pagination.complaints'));
        else $items = $items->get();

        return view('acciones.complain.list', array_merge([
            'items' => $items->appends($request->except('page')),
            'complaints_counter' => $this->complaintCounter(),
            'complaints_unassigned_counter' => $this->complaintsUnassignedCounter(),
            'export_link' => ['query' => $request->getQueryString(), 'params' => [
                'department' => $department,
            ], 'request' => 'failedIndex'],
        ], $this->filters($request)));
    }

    public function pendingIndex(Request $request, $department = null, $pagination = true, $export = false){
        if(Auth::user()->hasRole('admin') || Auth::user()->hasRole('operator'))
            $items = Complaint::latestFirst()->statusPending();
        elseif(Auth::user()->hasRole('supervisor')){
            $items = Complaint::leftJoin('complaint_assignments', function($join){
                $join->on('complaint_assignments.complaint_id', '=', 'complaint.id');
            })->latestFirst()->statusPending()
                ->whereIn('complaint_assignments.department_id', ($department ? [$department] : Auth::user()->departments()->lists('id')))
                ->groupBy('complaint_assignments.complaint_id');

        }elseif(Auth::user()->hasRole('fieldworker'))
            $items = Complaint::rightJoin('complaint_assignments', function($join){
                $join->on('complaint_assignments.complaint_id', '=', 'complaint.id');
            })->latestFirst()->statusPending()
                ->whereIn('complaint_assignments.employee_id', Auth::user()->employee->lists('id'))
                ->groupBy('complaint_assignments.complaint_id');
        else
            $items = Complaint::where('user_id', '=', DB::raw(Auth::user()->id))
                ->latestFirst()->statusPending();

        /* Add Filters to Model */
        $items = $this->queryFilter($items, $request);
        /* End Filters to Model */

        if(Auth::user()->hasRole('operator') || Auth::user()->hasRole('supervisor')  || Auth::user()->hasRole('fieldworker')) $items = $items->doesntHave('parent');

        if($export) return $items;

        if($pagination) $items = $items->paginate(config('csys.settings.pagination.complaints'));
        else $items = $items->get();

        return view('acciones.complain.list', array_merge([
            'items' => $items->appends($request->except('page')),
            'complaints_counter' => $this->complaintCounter(),
            'complaints_unassigned_counter' => $this->complaintsUnassignedCounter(),
            'export_link' => ['query' => $request->getQueryString(), 'params' => [
                'department' => $department,
            ], 'request' => 'pendingIndex'],
        ], $this->filters($request)));
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function resolvedIndex(Request $request, $department = null, $pagination = true, $export = false){
        if(Auth::user()->hasRole('admin') || Auth::user()->hasRole('operator'))
            $items = Complaint::latestFirst()->statusResolved();
        elseif(Auth::user()->hasRole('supervisor')) {
            $items = Complaint::leftJoin('complaint_assignments', function ($join) {
                $join->on('complaint_assignments.complaint_id', '=', 'complaint.id');
            })->latestFirst()->statusResolved()
                ->whereIn('complaint_assignments.department_id', ($department ? [$department] : Auth::user()->departments()->lists('id')))
                ->groupBy('complaint_assignments.complaint_id');

        }elseif(Auth::user()->hasRole('fieldworker'))
            $items = Complaint::leftJoin('complaint_assignments', function($join){
                $join->on('complaint_assignments.complaint_id', '=', 'complaint.id');
            })->latestFirst()->statusResolved()
                ->whereIn('complaint_assignments.employee_id', Auth::user()->employee->lists('id'))
                ->groupBy('complaint_assignments.complaint_id');
        else
            $items = Complaint::where('user_id', '=', DB::raw(Auth::user()->id))
                ->latestFirst()->statusResolved();

        /* Add Filters to Model */
        $items = $this->queryFilter($items, $request);
        /* End Filters to Model */

        if(Auth::user()->hasRole('operator') || Auth::user()->hasRole('supervisor')  || Auth::user()->hasRole('fieldworker')) $items = $items->doesntHave('parent');

        if($export) return $items;

        if($pagination) $items = $items->paginate(config('csys.settings.pagination.complaints'));
        else $items = $items->get();

        return view('acciones.complain.list', array_merge([
            'items' => $items->appends($request->except('page')),
            'complaints_counter' => $this->complaintCounter(),
            'complaints_unassigned_counter' => $this->complaintsUnassignedCounter(),
            'export_link' => ['query' => $request->getQueryString(), 'params' => [
                'department' => $department,
            ], 'request' => 'resolvedIndex'],
        ], $this->filters($request)));
    }

    public function rescheduleIndex(Request $request, $department = null, $pagination = true, $export = false){
        if(Auth::user()->hasRole('admin') || Auth::user()->hasRole('operator'))
            $items = Complaint::latestFirst()->statusReschedule();
        elseif(Auth::user()->hasRole('supervisor')) {
            $items = Complaint::leftJoin('complaint_assignments', function ($join) {
                $join->on('complaint_assignments.complaint_id', '=', 'complaint.id');
            })->latestFirst()->statusReschedule()
                ->whereIn('complaint_assignments.department_id', ($department ? [$department] : Auth::user()->departments()->lists('id')))
                ->groupBy('complaint_assignments.complaint_id');

        }elseif(Auth::user()->hasRole('fieldworker'))
            $items = Complaint::leftJoin('complaint_assignments', function($join){
                $join->on('complaint_assignments.complaint_id', '=', 'complaint.id');
            })->latestFirst()->statusReschedule()
                ->whereIn('complaint_assignments.employee_id', Auth::user()->employee->lists('id'))
                ->groupBy('complaint_assignments.complaint_id');
        else
            $items = Complaint::where('user_id', '=', DB::raw(Auth::user()->id))
                ->latestFirst()->statusReschedule();

        /* Add Filters to Model */
        $items = $this->queryFilter($items, $request);
        /* End Filters to Model */

        if(Auth::user()->hasRole('operator') || Auth::user()->hasRole('supervisor')  || Auth::user()->hasRole('fieldworker')) $items = $items->doesntHave('parent');

        if($export) return $items;

        if($pagination) $items = $items->paginate(config('csys.settings.pagination.complaints'));
        else $items = $items->get();

        return view('acciones.complain.list', array_merge([
            'items' => $items->appends($request->except('page')),
            'complaints_counter' => $this->complaintCounter(),
            'complaints_unassigned_counter' => $this->complaintsUnassignedCounter(),
            'export_link' => ['query' => $request->getQueryString(), 'params' => [
                'department' => $department,
            ], 'request' => 'rescheduleIndex'],
        ], $this->filters($request)));
    }

    public function get(Request $request, $status, $department = null, $pagination = true, $export = false){
        if(Auth::user()->hasRole('admin') || Auth::user()->hasRole('operator'))
            $items = Complaint::whereHas('state', function($q) use ($status){
                $q->where('short_code', '=', $status);
            })->latestFirst();
        elseif(Auth::user()->hasRole('supervisor')) {
            $items = Complaint::whereHas('state', function ($q) use ($status){
                $q->where('short_code', '=', $status);
            })->whereHas('assignments', function ($q) use ($department) {
                $q->whereIn('department_id', ($department ? [$department] : Auth::user()->departments()->lists('id')));
            })->latestFirst();

        }elseif(Auth::user()->hasRole('fieldworker'))
            $items = Complaint::whereHas('state', function($q) use ($status){
                $q->where('short_code', '=', $status);
            })->whereHas('assignments', function($q){
                $q->whereIn('employee_id', Auth::user()->employee->lists('id'));
            })->latestFirst();
        else
            $items = Complaint::whereHas('state', function($q) use ($status){
                $q->where('short_code', '=', $status);
            })->where('user_id', '=', Auth::user()->id)->latestFirst();

        /* Add Filters to Model */
        $items = $this->queryFilter($items, $request);
        /* End Filters to Model */

        if(Auth::user()->hasRole('operator') || Auth::user()->hasRole('supervisor')  || Auth::user()->hasRole('fieldworker')) $items = $items->doesntHave('parent');

        if($export) return $items;

        if($pagination) $items = $items->paginate(config('csys.settings.pagination.complaints'));
        else $items = $items->get();

        return view('acciones.complain.list', array_merge([
            'items' => $items->appends($request->except('page')),
            'complaints_counter' => $this->complaintCounter(),
            'complaints_unassigned_counter' => $this->complaintsUnassignedCounter(),
            'active_menu' => [
                'parent' => $department,
                'item' => $status
            ],
            'export_link' => ['query' => $request->getQueryString(), 'params' => (!is_null($department) ? [
                'status' => $status,
                'department' => $department,
            ] : ['status' => $status]), 'request' => 'get'],
        ], $this->filters($request)));
    }

    public function unassignedIndex(){
        if(Auth::user()->hasRole('admin') || Auth::user()->hasRole('operator'))
            $items = Complaint::latestFirst()->onlyUnassigned()->paginate(config('csys.settings.pagination.complaints'));
        else abort(403);

        return view('acciones.complain.list', [
            'items' => $items,
            'complaints_counter' => $this->complaintCounter(),
            'complaints_unassigned_counter' => $this->complaintsUnassignedCounter(),
        ]);
    }

    public function filters(Request $request){
        /*filter data*/
        $filters_data_container = [];
        $user = $filters_data_container['user'] = Auth::user();
        $user_is_super = $filters_data_container['user_is_super'] = $user->hasDepartments();

        if($user->hasRole('operator')){
            $sources_raw = ComplainSources::get();
            $sources = ['' => 'All'];
            foreach ($sources_raw as $source) {
                $sources[$source->id] = $source->title;
            }
            $filters_data_container['sources'] = $sources;

            $operators_raw = User::whereHas('roles', function ($query) { //commented, it will make problem in lots of user scenario
                $query->where('name', '=', 'operator');
            })->has('employee')->get();
            $operators = ['' => 'All'];
            foreach ($operators_raw as $operator) {
                $operators[$operator->id] = $operator->name;
            }
            $filters_data_container['operators'] = $operators;
        }

        if(($user->hasRole('supervisor') && !$user_is_super)) {
            $fieldworkers_raw = Employee::whereHas('users', function ($query) { //commented, it will make problem in lots of user scenario
                $query->whereHas('roles', function($q){
                    $q->where('name', '=', 'fieldworker');
                });
            })->whereHas('departments', function($q){
                $q->whereIn('departments.id', Auth::user()->departments()->pluck('id'));
            })->get();
            $fieldworkers = ['' => 'All'];
            foreach ($fieldworkers_raw as $fieldworker) {
                $fieldworkers[$fieldworker->id] = $fieldworker->name .' ('.$fieldworker->shortcode.')';
            }
            $filters_data_container['fieldworkers'] = $fieldworkers;
        }

        if($user->hasRole('supervisor') || $user->hasRole('fieldworker')) {
            $colonies_raw = ComplaintLocation::select('area')->groupBy('area')->get();
            $colonies = ['' => 'All'];
            foreach ($colonies_raw as $colony) {
                $colonies[$colony->area] = $colony->area;
            }
            $filters_data_container['colonies'] = $colonies;

            $blocks_raw = ComplaintLocation::select('block')->groupBy('block')->get();
            $blocks = ['' => 'All'];
            foreach ($blocks_raw as $block) {
                $blocks[$block->block] = $block->block;
            }
            $filters_data_container['blocks'] = $blocks;

            $streets_raw = ComplaintLocation::select('street')->groupBy('street')->get();
            $streets = ['' => 'All'];
            foreach ($streets_raw as $street) {
                $streets[$street->street] = $street->street;
            }
            $filters_data_container['streets'] = $streets;
        }

        if($user->hasRole('fieldworker') || $user_is_super) {
            $no_child_cat = ComplainCategories::whereDoesntHave('children')->where('parent', '=', 0)->get()->lists('id');
            $filtered_categories = ComplainCategories::where('parent', '>', '0')
                ->orWhere(function ($query) use ($no_child_cat) {
                    $query->whereIn('id', $no_child_cat->toArray());
                })->get();
            $categories = ['' => 'All'];
            foreach ($filtered_categories as $category) {
                $categories[$category->id] = $category->title;
            }
            $filters_data_container['categories'] = $categories;
        }

        //change array date to string
        //if($request->has('dates')) $request->request->set('dates', implode(' - ', $request->dates));

        $filters_data_container['filters'] = $request->all();

        return $filters_data_container;
    }

    public function queryFilter(Builder $query, Request $request){
        $items = $query;
        if ($request->has('source'))
            $items = $items->whereHas('sources', function ($q) use ($request) {
                $q->where('source_id', '=', $request->source);
            });
        if ($request->has('operator'))
            $items = $items->whereHas('creator', function ($q) use ($request) {
                $q->where('complaint_sources.creator_id', '=', $request->operator);
            });
        if ($request->has('fieldworker'))
            $items = $items->whereHas('assignments', function ($q) use ($request) {
                $q->where('employee_id', '=', $request->fieldworker);
            });
        if ($request->has('category'))
            $items = $items->where(function ($wheres) use ($request) {
                $wheres->where('category_id', '=', $request->category)
                    ->orWhere('child_category_id', '=', $request->category);
            });
        if ($request->has('complainer'))
            $items = $items->where('user_id', '=', $request->complainer);
        if($request->has('colony'))
            $items = $items->whereHas('location', function($q) use ($request){
                $q->where('area', '=', $request->colony);
            });
        if($request->has('block'))
            $items = $items->whereHas('location', function($q) use ($request){
                $q->where('block', '=', $request->block);
            });
        if ($request->has('street'))
            $items = $items->whereHas('location', function ($q) use ($request) {
                $q->where('street', '=', $request->street);
            });
        if ($request->has('dates')){
            $items = $items->whereBetween('complaint.created_at', app(GraphController::class)->filterDateRange($request->dates));
        }
        return $items;
    }

}
