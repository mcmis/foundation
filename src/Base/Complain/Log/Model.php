<?php

namespace MCMIS\Foundation\Base\Complain\Log;

use MCMIS\Foundation\BaseModel;
use MCMIS\Foundation\Traits\Complain\Log\AttributesTrait;
use MCMIS\Contracts\Foundation\Model\ComplainLog;

class Model extends BaseModel implements ComplainLog
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
