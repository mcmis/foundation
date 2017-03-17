<?php

namespace MCMIS\Foundation\Traits\Company\Employee;

use Carbon\Carbon;

trait AttributesTrait
{

    public function getNameAttribute()
    {
        return $this->attributes['first_name'] . (!empty($this->attributes['last_name']) ? ' ' . $this->attributes['last_name'] : '');
    }

    public function getDobAttribute()
    {
        return Carbon::parse($this->attributes['dob'])->format('m/d/Y');
    }

    public function getJoiningDateAttribute()
    {
        return Carbon::parse($this->attributes['joining_date'])->format('m/d/Y');
    }

    public function setDobAttribute($value)
    {
        $this->attributes['dob'] = Carbon::parse($value);
    }

    public function setJoiningDateAttribute($value)
    {
        $this->attributes['joining_date'] = Carbon::parse($value);
    }

}