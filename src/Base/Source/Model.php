<?php

namespace MCMIS\Foundation\Base\Source;

use Illuminate\Database\Eloquent\SoftDeletes;
use MCMIS\Foundation\BaseModel;
use MCMIS\Contracts\Foundation\Model\SourceContract;

class Model extends BaseModel implements SourceContract
{
    use SoftDeletes;

    protected $table = 'complain_sources';

    protected $fillable = [
        'title', 'description', 'location'
    ];

    public function complaints()
    {
        return $this->belongsToMany(sys('model.complain'), 'complaint_sources', 'source_id');
    }
}
