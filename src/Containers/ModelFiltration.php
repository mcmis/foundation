<?php

namespace MCMIS\Foundation\Container;


use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use MCMIS\Contracts\Report\ModelFiltration as Model;

class ModelFiltration implements Model
{
    protected $model;

    private $categories, $statuses;

    public function create($statuses, $categories)
    {
        $this->categories = $categories;
        $this->statuses = $statuses;


        $this->model = sys('model.category')->join('complaint', function ($complaint) use ($statuses, $categories) {
            $complaint->on('complain_categories.id', '=', DB::raw('(SELECT CASE WHEN complaint.child_category_id > 0
            THEN complaint.child_category_id ELSE complaint.category_id END)'))
                ->whereIn('complaint.status', $statuses)
                ->where(function ($query) use ($categories) {
                    $query->whereIn('complaint.category_id', $categories)
                        ->orWhereIn('complaint.child_category_id', $categories);
                });
        });

        return $this;
    }

    public function roleSpecified()
    {
        if (!Auth::user()->hasRole('admin')) {
            if (Auth::user()->hasRole('fieldworker')) {
                $this->model = $this->model->whereExists(function ($query) {
                    $query->select(DB::raw(1))->from('complaint_assignments')
                        ->whereRaw('complaint_assignments.complaint_id = complaint.id')
                        ->whereIn('complaint_assignments.employee_id', (count($employees = Auth::user()->employee->lists('id')->toArray()) ? $employees : [0]));
                });
            } elseif (Auth::user()->hasRole('supervisor')) {
                //specifically for assigned departments
                $this->model = $this->model->whereExists(function ($query) {
                    $query->select(DB::raw(1))->from('complaint_assignments')
                        ->whereRaw('complaint_assignments.complaint_id = complaint.id')
                        ->whereIn('complaint_assignments.department_id', Auth::user()->departments()->lists('id'));
                });
            } elseif (!Auth::user()->hasRole('operator'))
                $this->model = $this->model->where('complaint.user_id', '=', Auth::user()->id);
        }
        return $this;
    }

    public function get()
    {
        return $this->model;
    }
}
