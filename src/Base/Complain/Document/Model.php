<?php
namespace MCMIS\Foundation\Base\Complain\Document;

use MCMIS\Foundation\BaseModel;

class Model extends BaseModel
{
    protected $fillable = [
        'complaint_id', 'user_id', 'uri', 'caption'
    ];

    public function complaint()
    {
        return $this->belongsTo(app('model.complain'));
    }
}
