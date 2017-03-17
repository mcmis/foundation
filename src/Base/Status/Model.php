<?php

namespace MCMIS\Foundation\Base\Status;

use MCMIS\Foundation\BaseModel;

class Model extends BaseModel
{
    public $timestamps = false;

    protected $table = 'complain_status';

    protected $fillable = [
        'short_code', 'title', 'description'
    ];

    public function complaints()
    {
        return $this->hasMany(app('model.complain'), 'status');
    }

    public function comments()
    {
        return $this->hasMany(app('model.complain.comment'), 'status');
    }
}
