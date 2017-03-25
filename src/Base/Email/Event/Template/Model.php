<?php

namespace MCMIS\Foundation\Base\Email\Event\Template;

use MCMIS\Foundation\BaseModel;

class Model extends BaseModel
{
    protected $fillable = [
        'subject', 'body', 'user_id'
    ];

    public function event(){
        return $this->belongsTo(sys('model.email.event'), 'event_alias', 'alias');
    }
}
