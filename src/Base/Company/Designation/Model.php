<?php

namespace MCMIS\Foundation\Base\Company\Designation;

use Illuminate\Database\Eloquent\SoftDeletes;
use MCMIS\Foundation\BaseModel;
use MCMIS\Contracts\Foundation\Model\Designation;

class Model extends BaseModel implements Designation
{
    use SoftDeletes;

    protected $fillable = [
        'title', 'description'
    ];

    protected $table = 'designations';

    /*** Relationship ***/
    public function roles()
    {
        return $this->belongsToMany(sys('model.user.role'), 'designation_role');
    }

    public function employees()
    {
        return $this->hasMany(sys('model.company.employee'));
    }
}
