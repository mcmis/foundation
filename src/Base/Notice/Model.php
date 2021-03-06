<?php

namespace MCMIS\Foundation\Base\Notice;


use MCMIS\Foundation\BaseModel;
use MCMIS\Contracts\Foundation\Model\Notice;

class Model extends BaseModel implements Notice
{

    protected $table = 'user_notices';

    protected $fillable = [
        'sender', 'subject', 'msg', 'receiver'
    ];

    public function owner()
    {
        return $this->belongsTo(sys('model.user'), 'sender');
    }

    public function receiver()
    {
        return $this->belongsTo(sys('model.user'), 'receiver');
    }

    public function receivers()
    {
        return $this->hasMany(sys('model.user.notice'));
    }
}
