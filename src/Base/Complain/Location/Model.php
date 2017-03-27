<?php
namespace MCMIS\Foundation\Base\Complain\Location;

use MCMIS\Foundation\BaseModel;
use MCMIS\Contracts\Foundation\Model\ComplainLocation;

class Model extends BaseModel implements ComplainLocation
{

    protected $table = 'complaint_location';

    protected $fillable = [
        'complaint_id', 'area', 'block', 'street', 'street_another',
        'references', 'street_number', 'city',
        'preset_location_id'
    ];

    public function complaint()
    {
        return $this->belongsTo(sys('model.complain'));
    }

    public function presetLocation()
    {
        return $this->belongsTo(sys('model.location.preset'), 'preset_location_id');
    }

}
