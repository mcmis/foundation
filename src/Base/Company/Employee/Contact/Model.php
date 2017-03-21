<?php

namespace MCMIS\Foundation\Base\Company\Employee\Contact;

use MCMIS\Foundation\BaseModel;
use MCMIS\Contracts\Foundation\Model\EmployeeContact;

class Model extends BaseModel implements EmployeeContact
{

    protected $table = 'employee_contacts';

    protected $fillable = [
        'house', 'street_no', 'street', 'block', 'area', 'zipcode',
        'province', 'city', 'country', 'latlng', 'employee_id'
    ];

    public function employee()
    {
        return $this->belongsTo(sys('model.company.employee'));
    }
}
