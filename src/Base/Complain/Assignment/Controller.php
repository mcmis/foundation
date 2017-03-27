<?php

namespace MCMIS\Foundation\Base\Complain\Assignment;


use Illuminate\Support\Facades\Auth;
use MCMIS\Foundation\BaseController;

class Controller extends BaseController
{

    public function storeByEmployee(Request $request, $complain_no){
        if(!Auth::user()->hasRole('supervisor') && !Auth::user()->hasRole('admin')) abort(403);

        $complaint = sys('model.complain')->where('complain_no', '=', $complain_no)->first();
        if($assignment = sys('model.complain.assignment')->where('complaint_id', '=', $complaint->id)->whereIn('employee_id', Auth::user()->employee()->lists('employee_id'))->whereNotIn('employee_id', [$request->employee_id])->first()){
            $assigned = $complaint->assignments()->create([
                'assigner_id' => $assignment->employee_id,
                'employee_id' => $request->employee_id,
                'department_id' => $assignment->department_id,
            ]);
            if($assigned){
                $complaint->update(['status' => sys('model.status')->where('short_code', '=', 'assigned.staff')->first()->id]);
                flash()->success(trans('alert.complain.assignment.employee.assigned', ['complain_no' => $complain_no, 'employee' => $assigned->employee->name]));
                event('complaint.assigned.fieldworker', [$complaint, $assigned]);
            }else flash()->error(trans('alert.complain.assignment.employee.assign.fail'));
        }else{
            flash()->warning(trans('alert.complain.assignment.employee.warning.permission'));
        }
        return redirect()->action('ComplainsController@show', ['complain' => $complain_no]);
    }


    /* Update via json */
    public function forwordToDepartmentAjax($complain_no, $department_id, $employee_id){
        $complaint = sys('model.complain')->where('complain_no', '=', $complain_no)->first();
        event('complaint.assign.manually', [
            $complaint,
            $department_id,
            sys('model.company.employee')->find($employee_id) //operator id
        ]);
        $department = sys('model.company.department')->findOrFail($department_id);
        flash()->success(trans('alert.complain.assignment.requested', ['complain_no' => $complain_no, 'department' => $department->name]));

        return redirect()->action('ComplainsController@show', ['complain' => $complain_no]);
    }

}