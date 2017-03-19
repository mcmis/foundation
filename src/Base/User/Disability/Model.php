<?php

namespace MCMIS\Foundation\Base\User\Disability;

use Illuminate\Database\Eloquent\SoftDeletes;
use MCMIS\Foundation\BaseModel;

class Model extends BaseModel
{
    use SoftDeletes;

    protected $table = 'user_disability_types';

    protected $fillable = [
        'name'
    ];
}
