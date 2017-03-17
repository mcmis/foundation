<?php

namespace MCMIS\Foundation\Base\Company\Employee\Contact;


use MCMIS\Foundation\BaseModel;

class Model extends BaseModel
{

    protected $fillable = [
        'house', 'street_no', 'street', 'block', 'area', 'zipcode',
        'province', 'city', 'country', 'latlng', 'employee_id'
    ];

    public function employee()
    {
        return $this->belongsTo(app('model.company.employee'));
    }
}
