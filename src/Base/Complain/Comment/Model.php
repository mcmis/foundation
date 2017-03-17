<?php

namespace MCMIS\Foundation\Base\Complain\Comment;


use MCMIS\Foundation\BaseModel;
use MCMIS\Foundation\Traits\Complain\Comment\AttributesTrait;

class Model extends BaseModel
{

    use AttributesTrait;

    protected $fillable = [
        'complaint_id', 'user_id', 'msg', 'privacy', 'reply_of', 'priority', 'status', 'last_status', 'serial',
        'expected_completed_on', 'last_expected_completed_on', 'reschedule_on', 'last_reschedule_on'
    ];

    protected $dates = [
        'expected_completed_on',
        'last_expected_completed_on',
        'reschedule_on',
        'last_reschedule_on',
    ];

    public static function boot()
    {
        /** TODO: Transform it in observer. */
        parent::boot();

        static::saving(function ($post) {
            $total = parent::where('complaint_id', '=', $post->attributes['complaint_id'])->count();
            $post->attributes['serial'] = $total + 1;
        });
    }

    public function complaint()
    {
        return $this->belongsTo(app('model.complain'));
    }

    public function user()
    {
        return $this->belongsTo(app('model.user'), 'user_id')->withTrashed();
    }

    public function state()
    {
        return $this->belongsTo(app('model.status'), 'status');
    }
}
