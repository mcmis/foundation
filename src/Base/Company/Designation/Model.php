<?php

namespace MCMIS\Foundation\Base\Company\Designation;

use Illuminate\Database\Eloquent\SoftDeletes;
use MCMIS\Foundation\BaseModel;

class Model extends BaseModel
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
