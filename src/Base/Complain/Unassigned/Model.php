<?php

namespace MCMIS\Foundation\Base\Complain\Unassigned;

use MCMIS\Foundation\BaseModel;

class Model extends BaseModel
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
