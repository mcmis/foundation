<?php

namespace MCMIS\Foundation\Base\Company\Department;

use Illuminate\Database\Eloquent\SoftDeletes;
use MCMIS\Foundation\BaseModel;
use MCMIS\Contracts\Foundation\Model\Department;

class Model extends BaseModel implements Department
{
    use SoftDeletes;

    protected $table = 'departments';

    protected $fillable = [
        'name', 'description', 'shortcode', 'type'
    ];

    public function employees()
    {
        return $this->belongsToMany(sys('model.company.employee'), 'employee_department');
    }
}
