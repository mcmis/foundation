<?php

namespace MCMIS\Foundation\Base\Category\Option;

use MCMIS\Foundation\BaseModel;
use MCMIS\Contracts\Foundation\Model\CategoryOption;

class Model extends BaseModel implements CategoryOption
{

    protected $table = 'complain_category_options';

    protected $fillable = [
        'title', 'field', 'options', 'selected', 'visibility', 'complain_category_id'
    ];

    public function category()
    {
        return $this->belongsTo(sys('category'), 'complain_category_id');
    }

}
