<?php

namespace MCMIS\Foundation\Base\Company\Employee;


use Illuminate\Database\Eloquent\SoftDeletes;
use MCMIS\Foundation\BaseModel;
use MCMIS\Foundation\Traits\Company\Employee\AttributesTrait;
use MCMIS\Contracts\Foundation\Model\Employee;

class Model extends BaseModel implements Employee
{

    use AttributesTrait;
    use SoftDeletes;

    protected $table = 'employees';

    protected $fillable = [
        'first_name', 'last_name', 'dob', 'joining_date', 'shortcode', 'email', 'phone', 'ext', 'mobile',
        'designation_id', 'gender'
    ];

    protected $dates = [
        'dob',
        'joining_date'
    ];

    /**** Relationships ****/
    public function designation()
    {
        return $this->belongsTo(sys('model.company.designation'));
    }

    public function departments()
    {
        return $this->belongsToMany(sys('model.company.department'), 'employee_department');
    }

    public function contacts()
    {
        return $this->hasMany(sys('model.company.employee.contact'));
    }

    public function users()
    {
        return $this->belongsToMany(sys('model.user'), 'employee_user')->withTrashed();
    }

}
