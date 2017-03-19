<?php

namespace MCMIS\Foundation\Base\User\Notice;

use MCMIS\Foundation\BaseModel;

class Model extends BaseModel
{

    protected $table = 'user_notice_receivers';

    protected $fillable = [
        'user_notice_id', 'user_id', 'seen'
    ];

    public function receiver()
    {
        return $this->belongsTo(sys('model.user'), 'user_id');
    }

    public function notice()
    {
        return $this->belongsTo(sys('model.notice'), 'user_notice_id');
    }
}
