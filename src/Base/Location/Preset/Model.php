<?php

namespace MCMIS\Foundation\Base\Location\Preset;

use Illuminate\Database\Eloquent\SoftDeletes;
use MCMIS\Foundation\BaseModel;

class Model extends BaseModel
{
    use SoftDeletes;

    protected $table = 'preset_locations';

    protected $fillable = [
        'title', 'area_id', 'block_id', 'street_id', 'street_id_another', 'city',
        'street_number', 'references',
    ];

    public function area()
    {
        return $this->belongsTo(sys('model.location.area'), 'area_id')->withTrashed();
    }

    public function block()
    {
        return $this->belongsTo(sys('model.location.block'), 'block_id');
    }

    public function street()
    {
        return $this->belongsTo(sys('model.location.street'), 'street_id');
    }

    public function street_another()
    {
        return $this->belongsTo(sys('model.location.street'), 'street_id_another');
    }
}
