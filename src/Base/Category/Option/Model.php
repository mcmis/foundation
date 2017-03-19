<?php

namespace MCMIS\Foundation\Base\Category\Option;

use MCMIS\Foundation\BaseModel;

class Model extends BaseModel
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
