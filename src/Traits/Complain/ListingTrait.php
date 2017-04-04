<?php
namespace MCMIS\Foundation\Traits\Complain;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use MCMIS\Foundation\Base\Report\Controller;

trait ListingTrait
{

    public function index(Request $request, $pagination = true, $export = false){
        if(Auth::user()->hasRole('operator')){
            $items = sys('model.complain')->orderBy('complaint.updated_at', 'desc');
        }elseif(Auth::user()->hasRole('supervisor')){
            $items = sys('model.complain')->whereHas('assignments', function ($q) {
                $q->whereIn('department_id', Auth::user()->departments()->lists('id'));
            })->orderBy('complaint.updated_at', 'desc');

        }elseif(Auth::user()->hasRole('fieldworker')){
            $items = sys('model.complain')->whereHas('assignments', function ($q) {
                $q->whereIn('employee_id', Auth::user()->employee->lists('id'));
            })->orderBy('complaint.updated_at', 'desc');
        }else
            $items = sys('model.complain')->where('user_id', '=', DB::raw(Auth::user()->id))
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

    public function queryFilter(Builder $query, Request $request)
    {
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
        if ($request->has('colony'))
            $items = $items->whereHas('location', function ($q) use ($request) {
                $q->where('area', '=', $request->colony);
            });
        if ($request->has('block'))
            $items = $items->whereHas('location', function ($q) use ($request) {
                $q->where('block', '=', $request->block);
            });
        if ($request->has('street'))
            $items = $items->whereHas('location', function ($q) use ($request) {
                $q->where('street', '=', $request->street);
            });
        if ($request->has('dates')) {
            $items = $items->whereBetween('complaint.created_at', app(Controller::class)->filterDateRange($request->dates));
        }
        if($request->has('name'))
            $items = $items->whereHas('user', function($q) use ($request){
                $q->where('name', 'like', '%'.$request->name.'%');
            });
        if($request->has('email'))
            $items = $items->whereHas('user', function($q) use ($request){
                $q->where('email', '=', $request->email);
            });
        if($request->has('mobile'))
            $items = $items->whereHas('user', function($q) use ($request){
                $q->where('mobile', '=', $request->mobile);
            });
        if($request->has('complain_no'))
            $items = $items->where('complain_no', '=', $request->complain_no);
        if($request->has('external_ref'))
            $items = $items->where('external_ref', '=', $request->external_ref);
        return $items;
    }

    public function get(Request $request, $status, $department = null, $pagination = true, $export = false)
    {
        if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('operator'))
            $items = sys('model.complain')->whereHas('state', function ($q) use ($status) {
                $q->where('short_code', '=', $status);
            })->latestFirst();
        elseif (Auth::user()->hasRole('supervisor')) {
            $items = sys('model.complain')->whereHas('state', function ($q) use ($status) {
                $q->where('short_code', '=', $status);
            })->whereHas('assignments', function ($q) use ($department) {
                $q->whereIn('department_id', ($department ? [$department] : Auth::user()->departments()->lists('id')));
            })->latestFirst();

        } elseif (Auth::user()->hasRole('fieldworker'))
            $items = sys('model.complain')->whereHas('state', function ($q) use ($status) {
                $q->where('short_code', '=', $status);
            })->whereHas('assignments', function ($q) {
                $q->whereIn('employee_id', Auth::user()->employee->lists('id'));
            })->latestFirst();
        else
            $items = sys('model.complain')->whereHas('state', function ($q) use ($status) {
                $q->where('short_code', '=', $status);
            })->where('user_id', '=', Auth::user()->id)->latestFirst();

        /* Add Filters to Model */
        $items = $this->queryFilter($items, $request);
        /* End Filters to Model */

        if (Auth::user()->hasRole('operator') || Auth::user()->hasRole('supervisor') || Auth::user()->hasRole('fieldworker')) $items = $items->doesntHave('parent');

        if ($export) return $items;

        if ($pagination) $items = $items->paginate(config('csys.settings.pagination.complaints'));
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

    public function filters(Request $request)
    {
        /*filter data*/
        $filters_data_container = [];
        $user = $filters_data_container['user'] = Auth::user();
        $user_is_super = $filters_data_container['user_is_super'] = $user->hasDepartments();

        if ($user->hasRole('operator')) {
            $sources_raw = sys('model.source')->get();
            $sources = ['' => 'All'];
            foreach ($sources_raw as $source) {
                $sources[$source->id] = $source->title;
            }
            $filters_data_container['sources'] = $sources;

            $operators_raw = sys('model.user')->whereHas('roles', function ($query) { //commented, it will make problem in lots of user scenario
                $query->where('name', '=', 'operator');
            })->has('employee')->get();
            $operators = ['' => 'All'];
            foreach ($operators_raw as $operator) {
                $operators[$operator->id] = $operator->name;
            }
            $filters_data_container['operators'] = $operators;
        }

        if (($user->hasRole('supervisor') && !$user_is_super)) {
            $fieldworkers_raw = sys('model.company.employee')->whereHas('users', function ($query) { //commented, it will make problem in lots of user scenario
                $query->whereHas('roles', function ($q) {
                    $q->where('name', '=', 'fieldworker');
                });
            })->whereHas('departments', function ($q) {
                $q->whereIn('departments.id', Auth::user()->departments()->pluck('id'));
            })->get();
            $fieldworkers = ['' => 'All'];
            foreach ($fieldworkers_raw as $fieldworker) {
                $fieldworkers[$fieldworker->id] = $fieldworker->name . ' (' . $fieldworker->shortcode . ')';
            }
            $filters_data_container['fieldworkers'] = $fieldworkers;
        }

        if ($user->hasRole('supervisor') || $user->hasRole('fieldworker')) {
            $colonies_raw = sys('model.complain.location')->select('area')->groupBy('area')->get();
            $colonies = ['' => 'All'];
            foreach ($colonies_raw as $colony) {
                $colonies[$colony->area] = $colony->area;
            }
            $filters_data_container['colonies'] = $colonies;

            $blocks_raw = sys('model.complain.location')->select('block')->groupBy('block')->get();
            $blocks = ['' => 'All'];
            foreach ($blocks_raw as $block) {
                $blocks[$block->block] = $block->block;
            }
            $filters_data_container['blocks'] = $blocks;

            $streets_raw = sys('model.complain.location')->select('street')->groupBy('street')->get();
            $streets = ['' => 'All'];
            foreach ($streets_raw as $street) {
                $streets[$street->street] = $street->street;
            }
            $filters_data_container['streets'] = $streets;
        }

        if ($user->hasRole('fieldworker') || $user_is_super) {
            $no_child_cat = sys('model.category')->whereDoesntHave('children')->where('parent', '=', 0)->get()->lists('id');
            $filtered_categories = sys('model.category')->where('parent', '>', '0')
                ->orWhere(function ($query) use ($no_child_cat) {
                    $query->whereIn('id', $no_child_cat->toArray());
                })->get();
            $categories = ['' => 'All'];
            foreach ($filtered_categories as $category) {
                $categories[$category->id] = $category->title;
            }
            $filters_data_container['categories'] = $categories;
        }

        $filters_data_container['filters'] = $request->all();

        return $filters_data_container;
    }

    public function verifiedIndex(Request $request, $department = null, $pagination = true, $export = false){
        if(Auth::user()->hasRole('admin') || Auth::user()->hasRole('operator'))
            $items = sys('model.complain')->latestFirst()->statusVerified();
        elseif(Auth::user()->hasRole('supervisor')){
            $items = sys('model.complain')->leftJoin('complaint_assignments', function ($join) {
                $join->on('complaint_assignments.complaint_id', '=', 'complaint.id');
            })->latestFirst()->statusVerified()
                ->whereIn('complaint_assignments.department_id', ($department ? [$department] : Auth::user()->departments()->lists('id')))
                ->groupBy('complaint_assignments.complaint_id');

        }elseif(Auth::user()->hasRole('fieldworker'))
            $items = sys('model.complain')->whereHas('assignments', function ($q) {
                $q->whereIn('employee_id', Auth::user()->employee->lists('id'));
            })->latestFirst()->statusVerified();
        else
            $items = sys('model.complain')->where('user_id', '=', DB::raw(Auth::user()->id))
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
            $items = sys('model.complain')->latestFirst()->statusInprocess();
        elseif(Auth::user()->hasRole('supervisor')){
            $items = sys('model.complain')->leftJoin('complaint_assignments', function ($join) {
                $join->on('complaint_assignments.complaint_id', '=', 'complaint.id');
            })->latestFirst()->statusInprocess()
                ->whereIn('complaint_assignments.department_id', ($department ? [$department] : Auth::user()->departments()->lists('id')))
                ->groupBy('complaint_assignments.complaint_id');

        }elseif(Auth::user()->hasRole('fieldworker'))
            $items = sys('model.complain')->whereHas('assignments', function ($q) {
                $q->whereIn('employee_id', Auth::user()->employee->lists('id'));
            })->latestFirst()->statusInprocess();
        else
            $items = sys('model.complain')->where('user_id', '=', DB::raw(Auth::user()->id))
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
            $items = sys('model.complain')->latestFirst()->statusDiscard();
        elseif(Auth::user()->hasRole('supervisor')){
            $items = sys('model.complain')->leftJoin('complaint_assignments', function ($join) {
                $join->on('complaint_assignments.complaint_id', '=', 'complaint.id');
            })->latestFirst()->statusDiscard()
                ->whereIn('complaint_assignments.department_id', ($department ? [$department] : Auth::user()->departments()->lists('id')))
                ->groupBy('complaint_assignments.complaint_id');

        }elseif(Auth::user()->hasRole('fieldworker'))
            $items = sys('model.complain')->whereHas('assignments', function ($q) {
                $q->whereIn('employee_id', Auth::user()->employee->lists('id'));
            })->latestFirst()->statusDiscard();
        else
            $items = sys('model.complain')->where('user_id', '=', DB::raw(Auth::user()->id))
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
            $items = sys('model.complain')->latestFirst()->statusFailed();
        elseif(Auth::user()->hasRole('supervisor')){
            $items = sys('model.complain')->leftJoin('complaint_assignments', function ($join) {
                $join->on('complaint_assignments.complaint_id', '=', 'complaint.id');
            })->latestFirst()->statusFailed()
                ->whereIn('complaint_assignments.department_id', ($department ? [$department] : Auth::user()->departments()->lists('id')))
                ->groupBy('complaint_assignments.complaint_id');

        }elseif(Auth::user()->hasRole('fieldworker'))
            $items = sys('model.complain')->whereHas('assignments', function ($q) {
                $q->whereIn('employee_id', Auth::user()->employee->lists('id'));
            })->latestFirst()->statusFailed();
        else
            $items = sys('model.complain')->where('user_id', '=', DB::raw(Auth::user()->id))
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
            $items = sys('model.complain')->latestFirst()->statusPending();
        elseif(Auth::user()->hasRole('supervisor')){
            $items = sys('model.complain')->leftJoin('complaint_assignments', function ($join) {
                $join->on('complaint_assignments.complaint_id', '=', 'complaint.id');
            })->latestFirst()->statusPending()
                ->whereIn('complaint_assignments.department_id', ($department ? [$department] : Auth::user()->departments()->lists('id')))
                ->groupBy('complaint_assignments.complaint_id');

        }elseif(Auth::user()->hasRole('fieldworker'))
            $items = sys('model.complain')->whereHas('assignments', function ($q) {
                $q->whereIn('employee_id', Auth::user()->employee->lists('id'));
            })->latestFirst()->statusPending();
        else
            $items = sys('model.complain')->where('user_id', '=', DB::raw(Auth::user()->id))
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
            $items = sys('model.complain')->latestFirst()->statusResolved();
        elseif(Auth::user()->hasRole('supervisor')) {
            $items = sys('model.complain')->leftJoin('complaint_assignments', function ($join) {
                $join->on('complaint_assignments.complaint_id', '=', 'complaint.id');
            })->latestFirst()->statusResolved()
                ->whereIn('complaint_assignments.department_id', ($department ? [$department] : Auth::user()->departments()->lists('id')))
                ->groupBy('complaint_assignments.complaint_id');

        }elseif(Auth::user()->hasRole('fieldworker'))
            $items = sys('model.complain')->whereHas('assignments', function ($q) {
                $q->whereIn('employee_id', Auth::user()->employee->lists('id'));
            })->latestFirst()->statusResolved();
        else
            $items = sys('model.complain')->where('user_id', '=', DB::raw(Auth::user()->id))
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
            $items = sys('model.complain')->latestFirst()->statusReschedule();
        elseif(Auth::user()->hasRole('supervisor')) {
            $items = sys('model.complain')->leftJoin('complaint_assignments', function ($join) {
                $join->on('complaint_assignments.complaint_id', '=', 'complaint.id');
            })->latestFirst()->statusReschedule()
                ->whereIn('complaint_assignments.department_id', ($department ? [$department] : Auth::user()->departments()->lists('id')))
                ->groupBy('complaint_assignments.complaint_id');

        }elseif(Auth::user()->hasRole('fieldworker'))
            $items = sys('model.complain')->whereHas('assignments', function ($q) {
                $q->whereIn('employee_id', Auth::user()->employee->lists('id'));
            })->latestFirst()->statusReschedule();
        else
            $items = sys('model.complain')->where('user_id', '=', DB::raw(Auth::user()->id))
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

    public function unassignedIndex(){
        if(Auth::user()->hasRole('admin') || Auth::user()->hasRole('operator'))
            $items = sys('model.complain')->latestFirst()->onlyUnassigned()->paginate(config('csys.settings.pagination.complaints'));
        else abort(403);

        return view('acciones.complain.list', [
            'items' => $items,
            'complaints_counter' => $this->complaintCounter(),
            'complaints_unassigned_counter' => $this->complaintsUnassignedCounter(),
        ]);
    }

}
