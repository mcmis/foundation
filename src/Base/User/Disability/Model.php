<?php

namespace MCMIS\Foundation\Base\User\Disability;

use Illuminate\Database\Eloquent\SoftDeletes;
use MCMIS\Foundation\BaseModel;
use MCMIS\Contracts\Foundation\Model\UserDisabilityContract;

class Model extends BaseModel implements UserDisabilityContract
{
    use SoftDeletes;

    protected $table = 'user_disability_types';

    protected $fillable = [
        'name'
    ];
}
