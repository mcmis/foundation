<?php

namespace MCMIS\Foundation\Base\Source;

use Illuminate\Database\Eloquent\SoftDeletes;
use MCMIS\Foundation\BaseModel;

class Model extends BaseModel
{
    use SoftDeletes;

    protected $fillable = [
        'title', 'description', 'location'
    ];

    public function complaints()
    {
        return $this->belongsToMany(app('model.complain'), 'complaint_sources', 'source_id');
    }
}
