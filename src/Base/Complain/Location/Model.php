<?php
namespace MCMIS\Foundation\Base\Complain\Location;

use MCMIS\Foundation\BaseModel;


class Model extends BaseModel
{

    protected $table = 'complaint_location';

    protected $fillable = [
        'complaint_id', 'area', 'block', 'street', 'street_another',
        'references', 'street_number', 'city',
        'preset_location_id'
    ];

    public function complaint()
    {
        return $this->belongsTo(app('model.complain'));
    }

    public function presetLocation()
    {
        return $this->belongsTo(app('model.location.preset'), 'preset_location_id');
    }

}
