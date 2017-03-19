<?php
namespace MCMIS\Foundation\Traits\Report;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

trait FiltersTrait
{
    public function filtersData()
    {
        /*filter data*/
        $filters_data_container = [];
        $user = $filters_data_container['user'] = Auth::user();
        $user_is_super = $filters_data_container['user_is_super'] = $user->hasDepartments();

        if ($user->hasRole('admin')) {
            $sources_raw = sys('model.source')->all();
            $sources = ['' => 'All'];
            foreach ($sources_raw as $source) {
                $sources[$source->id] = $source->title;
            }
            $filters_data_container['sources'] = $sources;
        }

        if ($user->hasRole('supervisor')) {
            if ($user_is_super) {
                $departments_raw = $user->departments();
                $departments = ['' => 'All'];
                foreach ($departments_raw as $department) {
                    $departments[$department->id] = $department->name;
                }
                $filters_data_container['departments'] = $departments;
            }

            $fieldworkers_raw = sys('model.company.employee')->whereHas('users', function ($query) {
                $query->whereHas('roles', function ($query) {
                    $query->where('name', '=', 'fieldworker');
                });
            })->whereHas('departments', function ($dquery) use ($user) {
                $dquery->whereIn('departments.id', $user->departments()->lists('id')->toArray());
            })->get();
            $fieldworkers = ['' => 'All'];
            foreach ($fieldworkers_raw as $fieldworker) {
                $fieldworkers[$fieldworker->id] = $fieldworker->first_name . ' ' . $fieldworker->last_name . '(' . $fieldworker->departments->implode('name', ',') . ')';
            }
            $filters_data_container['fieldworkers'] = $fieldworkers;

            if (!$user_is_super) {
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
        }

        if (Auth::user()->hasRole('supervisor') || Auth::user()->hasRole('fieldworker')) {
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
            $statuses_raw = sys('model.status')->whereIn('short_code',
                $this->workflow->canView(Auth::user()->hasRole('supervisor') ? 'supervisor' : 'fieldworker'))
                ->orderBy('id')->get();
            $statuses = ['' => 'All'];
            foreach ($statuses_raw as $status) {
                $statuses[$status->id] = $status->title;
            }
            $filters_data_container['statuses'] = $statuses;
        }

        return $filters_data_container;
    }

    public function addRequestedFilters(Request $request, $model)
    {
        if ($request->has('source')) {
            $model = $model->join('complaint_sources', function ($join) use ($request) {
                $join->on('complaint.id', '=', 'complaint_sources.complaint_id')
                    ->where('complaint_sources.source_id', '=', $request->source);
            });
        }
        if ($request->has('department')) {
            $model = $model->whereExists(function ($query) use ($request) {
                $query->select(DB::raw(1))->from('complaint_assignments')
                    ->whereRaw('complaint_assignments.complaint_id = complaint.id')
                    ->whereRaw('complaint_assignments.department_id = ' . $request->department);
            });
        }
        if ($request->has('fieldworker')) {
            $model = $model->whereExists(function ($query) use ($request) {
                $query->select(DB::raw(1))->from('complaint_assignments')
                    ->whereRaw('complaint_assignments.complaint_id = complaint.id')
                    ->whereRaw('complaint_assignments.employee_id = ' . $request->fieldworker);
            });
        }
        if ($request->has('complainer')) {
            $model = $model->where('complaint.user_id', '=', $request->complainer);
        }

        if ($request->has('dates')) {
            $model = $model->whereBetween('complaint.created_at', $this->filterDateRange($request->dates));
        }

        if ($request->has('colony')) {
            $model = $model->whereExists(function ($query) use ($request) {
                $query->select(DB::raw(1))->from('complaint_location')
                    ->whereRaw('complaint_location.complaint_id = complaint.id 
                    and complaint_location.area = "' . $request->colony . '"');
            });
        }

        if ($request->has('block')) {
            $model = $model->whereExists(function ($query) use ($request) {
                $query->select(DB::raw(1))->from('complaint_location')
                    ->whereRaw('complaint_location.complaint_id = complaint.id
                    and complaint_location.block = "' . $request->block . '"');
            });
        }

        if ($request->has('street')) {
            $model = $model->whereExists(function ($query) use ($request) {
                $query->select(DB::raw(1))->from('complaint_location')
                    ->whereRaw('complaint_location.complaint_id = complaint.id
                    and complaint_location.street = "' . $request->street . '"');
            });
        }

        return $model;
    }

    public function filterDateRange($dates = null)
    {
        if ($dates) $dates = array_map(function ($value) {
            return Carbon::parse($value)->format('Y-m-d');
        }, array_combine(['start', 'end'], explode(' - ', $dates)));
        return $dates;
    }

    public function filteredComplaints($categories = [], $status = [])
    {
        $output = sys('model.complain')->where(function ($query) use ($categories) {
            $query->whereIn('category_id', $categories)
                ->orWhereIn('child_category_id', $categories);
        })->whereIn('status', $status);
        if (!Auth::user()->hasRole('admin')) {
            if (Auth::user()->hasRole('fieldworker'))
                $output = $output->join('complaint_assignments', function ($join) {
                    $join->on('complaint.id', '=', 'complaint_assignments.complaint_id')
                        ->whereIn('complaint_assignments.employee_id', (count($employees = Auth::user()->employee->lists('id')->toArray()) ? $employees : [0]));
                });
            elseif (Auth::user()->hasRole('supervisor'))
                $output = $output->join('complaint_assignments', function ($join) {
                    $join->on('complaint.id', '=', 'complaint_assignments.complaint_id')
                        ->whereIn('complaint_assignments.department_id', Auth::user()->departments()->lists('id'));
                })->groupBy('complaint_assignments.complaint_id');
            elseif (!Auth::user()->hasRole('operator'))
                $output = $output->where('complaint.user_id', '=', Auth::user()->id);
        }
        return $output->get();
    }
}
