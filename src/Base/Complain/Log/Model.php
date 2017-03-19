<?php

namespace MCMIS\Foundation\Base\Complain\Log;

use MCMIS\Foundation\BaseModel;
use MCMIS\Foundation\Traits\Complain\Log\AttributesTrait;

class Model extends BaseModel
{
    use AttributesTrait;

    protected $table = 'complain_request_logs';

    protected $fillable = [
        'request', 'complain_no'
    ];

    public function complaint()
    {
        return $this->belongsTo(sys('model.complain'), 'complain_no', 'complain_no');
    }
}
