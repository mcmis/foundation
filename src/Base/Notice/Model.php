<?php

namespace MCMIS\Foundation\Base\Notice;


use MCMIS\Foundation\BaseModel;

class Model extends BaseModel
{
    protected $fillable = [
        'sender', 'subject', 'msg', 'receiver'
    ];

    public function owner()
    {
        return $this->belongsTo(app('model.user'), 'sender');
    }

    public function receiver()
    {
        return $this->belongsTo(app('model.user'), 'receiver');
    }

    public function receivers()
    {
        return $this->hasMany(app('model.user.notice'));
    }
}
