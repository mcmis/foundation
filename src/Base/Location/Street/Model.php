<?php

namespace MCMIS\Foundation\Base\Location\Street;

use MCMIS\Foundation\BaseModel;

class Model extends BaseModel
{
    protected $table = 'ls_streets';

    protected $fillable = ['name', 'block_id'];

    public function block()
    {
        return $this->belongsTo(app('model.location.block'), 'block_id');
    }
}
