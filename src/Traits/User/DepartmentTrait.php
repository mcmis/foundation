<?php
namespace MCMIS\Foundation\Traits\User;


trait DepartmentTrait
{
    public function hasDepartments()
    {
        return count($this->departments()) > 1 ? true : false;
    }

    public function departments()
    {
        $employees_id = $this->employee->lists('id')->toArray();
        return app('model.company.department')->whereHas('employees', function ($query) use ($employees_id) {
            $query->whereIn('employees.id', $employees_id);
        })->get();
    }

}