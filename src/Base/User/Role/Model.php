<?php
namespace MCMIS\Foundation\Base\User\Role;

use MCMIS\Foundation\Traits\User\Role\ScopesTrait;
use Zizaco\Entrust\EntrustRole;

class Model extends EntrustRole
{
    use ScopesTrait;

    protected $fillable = [
        'name', 'display_name', 'description'
    ];

    public function designations()
    {
        return $this->belongsToMany(app('model.company.designation'), 'designation_role');
    }

}
