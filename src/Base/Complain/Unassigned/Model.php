<?php

namespace MCMIS\Foundation\Base\Complain\Unassigned;

use MCMIS\Foundation\BaseModel;
use MCMIS\Contracts\Foundation\Model\ComplainUnassignedContract;

class Model extends BaseModel implements ComplainUnassignedContract
{
    public $timestamps = false;

    protected $table = 'complaints_unassigned';

    protected $fillable = [
        'complaint_id'
    ];

    public function complaint()
    {
        return $this->belongsTo(sys('model.complain'));
    }
}
