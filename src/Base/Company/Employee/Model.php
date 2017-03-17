<?php

namespace MCMIS\Foundation\Base\Company\Employee;


use Illuminate\Database\Eloquent\SoftDeletes;
use MCMIS\Foundation\BaseModel;
use MCMIS\Foundation\Traits\Company\Employee\AttributesTrait;

class Model extends BaseModel
{

    use AttributesTrait;
    use SoftDeletes;

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
        return $this->belongsTo(app('model.company.designation'));
    }

    public function departments()
    {
        return $this->belongsToMany(app('model.company.department'), 'employee_department');
    }

    public function contacts()
    {
        return $this->hasMany(app('model.company.employee.contact'));
    }

    public function users()
    {
        return $this->belongsToMany(app('model.user'), 'employee_user')->withTrashed();
    }

}
