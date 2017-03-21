<?php

namespace MCMIS\Foundation\Base\Complain\Comment;


use MCMIS\Foundation\BaseModel;
use MCMIS\Foundation\Traits\Complain\Comment\AttributesTrait;
use MCMIS\Foundation\Traits\Complain\Comment\ScopesTrait;
use MCMIS\Contracts\Foundation\Model\ComplainComment;

class Model extends BaseModel implements ComplainComment
{

    use AttributesTrait, ScopesTrait;

    protected $table = 'complaint_comments';

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
        return $this->belongsTo(sys('model.complain'));
    }

    public function user()
    {
        return $this->belongsTo(sys('model.user'), 'user_id')->withTrashed();
    }

    public function state()
    {
        return $this->belongsTo(sys('model.status'), 'status');
    }
}
