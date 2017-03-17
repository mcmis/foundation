<?php

namespace MCMIS\Foundation\Base\Category\Option;

use MCMIS\Foundation\BaseModel;

class Model extends BaseModel
{
    protected $fillable = [
        'title', 'field', 'options', 'selected', 'visibility', 'complain_category_id'
    ];

    public function category()
    {
        return $this->belongsTo(app('category'), 'complain_category_id');
    }

}
