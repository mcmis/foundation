<?php

namespace MCMIS\Foundation\Traits\User;

use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

trait AttributesTrait
{

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::needsRehash($value) ? Hash::make($value) : $value;
    }

    public function setAgeAttribute($value)
    {
        if (!empty($value)) {
            $tmp_date = Carbon::parse($value);
            $this->attributes['age'] = $tmp_date->format('Y/m/d');
        } else $this->attributes['age'] = null;
    }

    public function getAgeAttribute()
    {
        return $this->attributes['age'] ? Carbon::parse($this->attributes['age'])->format('d/m/Y') : null;
    }

}