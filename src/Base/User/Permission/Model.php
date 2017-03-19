<?php

namespace MCMIS\Foundation\Base\User\Permission;

use Zizaco\Entrust\EntrustPermission;

class Model extends EntrustPermission
{

    protected $table = 'permissions';

    protected $fillable = [
        'name', 'display_name', 'description'
    ];
}
