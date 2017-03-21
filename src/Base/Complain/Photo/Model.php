<?php

namespace MCMIS\Foundation\Base\Complain\Photo;

use MCMIS\Foundation\BaseModel;
use MCMIS\Contracts\Foundation\Model\ComplainPhoto;

class Model extends BaseModel implements ComplainPhoto
{

    protected $table = 'complaint_photos';

    protected $fillable = [
        'complaint_id', 'user_id', 'uri', 'caption'
    ];

    public function complaint()
    {
        return $this->belongsTo(sys('model.complain'));
    }
}
