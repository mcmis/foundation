<?php

namespace MCMIS\Foundation\Base\Complain\Log;

use MCMIS\Foundation\BaseModel;
use MCMIS\Foundation\Traits\Complain\Log\AttributesTrait;

class Model extends BaseModel
{
    use AttributesTrait;

    protected $fillable = [
        'request', 'complain_no'
    ];

    public function complaint()
    {
        return $this->belongsTo(app('model.complain'), 'complain_no', 'complain_no');
    }
}
