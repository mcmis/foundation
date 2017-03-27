<?php

namespace MCMIS\Foundation\Base\Email\Event;

use MCMIS\Foundation\BaseModel;

class Model extends BaseModel
{
    protected $table = 'email_template_events';

    protected $fillable = [
        'title', 'alias'
    ];

    public function template(){
        return $this->hasOne(sys('model.email.event.template'), 'event_alias', 'alias');
    }
}
