<?php

namespace MCMIS\Foundation\Base\Complain\Option;

use MCMIS\Foundation\BaseModel;

class Model extends BaseModel
{
    protected $fillable = [
        'complaint_id', 'complain_category_option_id', 'value'
    ];

    protected $dates = ['created_at', 'updated_at'];

    public function complaint()
    {
        return $this->belongsTo(app('model.complain'), 'complaint_id');
    }

    public function option()
    {
        return $this->belongsTo(app('model.category.option'), 'complain_category_option_id');
    }
}
