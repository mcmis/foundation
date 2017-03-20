<?php

namespace MCMIS\Foundation\Base\Location\Street;

use MCMIS\Foundation\BaseModel;
use MCMIS\Contracts\Foundation\Model\LocationStreetContract;

class Model extends BaseModel implements LocationStreetContract
{
    protected $table = 'ls_streets';

    protected $fillable = ['name', 'block_id'];

    public function block()
    {
        return $this->belongsTo(sys('model.location.block'), 'block_id');
    }
}
