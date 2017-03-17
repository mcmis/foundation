<?php

namespace MCMIS\Foundation\Base\User\Notice;

use MCMIS\Foundation\BaseModel;

class Model extends BaseModel
{
    protected $fillable = [
        'user_notice_id', 'user_id', 'seen'
    ];

    public function receiver()
    {
        return $this->belongsTo(app('model.user'), 'user_id');
    }

    public function notice()
    {
        return $this->belongsTo(app('model.notice'), 'user_notice_id');
    }
}
