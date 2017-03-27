<?php

namespace MCMIS\Foundation\Base\Complain\Option;

use MCMIS\Foundation\BaseModel;
use MCMIS\Contracts\Foundation\Model\ComplainOption;

class Model extends BaseModel implements ComplainOption
{

    protected $table = 'complaint_category_options';

    protected $fillable = [
        'complaint_id', 'complain_category_option_id', 'value'
    ];

    protected $dates = ['created_at', 'updated_at'];

    public function complaint()
    {
        return $this->belongsTo(sys('model.complain'), 'complaint_id');
    }

    public function option()
    {
        return $this->belongsTo(sys('model.category.option'), 'complain_category_option_id');
    }
}
