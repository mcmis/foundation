<?php

namespace MCMIS\Foundation\Base\Complain\Unassigned;

use MCMIS\Foundation\BaseModel;
use MCMIS\Contracts\Foundation\Model\ComplainUnassigned;

class Model extends BaseModel implements ComplainUnassigned
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
