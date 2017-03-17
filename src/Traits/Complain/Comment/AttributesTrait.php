<?php
namespace MCMIS\Foundation\Traits\Complain\Comment;

use Carbon\Carbon;

trait AttributesTrait
{

    public function getPriorityAttribute($value)
    {
        switch ($value) {
            case 1:
                $output = 'High';
                break;
            case 2:
                $output = 'Normal';
                break;
            default:
                $output = 'Low';
        }
        return $output;
    }

    public function getPriorityCodeAttribute()
    {
        return $this->attributes['priority'];
    }

    public function setExpectedCompletedOnAttribute($value)
    {
        if ($value) {
            $tmp_date = Carbon::createFromFormat('m/d/Y', $value);
            $this->attributes['expected_completed_on'] = $tmp_date->format('Y/m/d');
        }
    }

    public function getExpectedCompletedOnAttribute()
    {
        return $this->attributes['expected_completed_on'] ? Carbon::parse($this->attributes['expected_completed_on'])->format('m/d/Y') : '';
    }

    public function setLastExpectedCompletedOnAttribute($value)
    {
        if ($value) {
            $tmp_date = Carbon::createFromFormat('m/d/Y', $value);
            $this->attributes['last_expected_completed_on'] = $tmp_date->format('Y/m/d');
        }
    }

    public function getLastExpectedCompletedOnAttribute()
    {
        return $this->attributes['last_expected_completed_on'] ? Carbon::parse($this->attributes['last_expected_completed_on'])->format('m/d/Y') : '';
    }

    public function setRescheduleOnAttribute($value)
    {
        if ($value) {
            $tmp_date = Carbon::createFromFormat('m/d/Y', $value);
            $this->attributes['reschedule_on'] = $tmp_date->format('Y/m/d');
        }
    }

    public function getRescheduleOnAttribute()
    {
        return $this->attributes['reschedule_on'] ? Carbon::parse($this->attributes['reschedule_on'])->format('m/d/Y') : '';
    }

    public function setLastRescheduleOnAttribute($value)
    {
        if ($value) {
            $tmp_date = Carbon::createFromFormat('m/d/Y', $value);
            $this->attributes['last_reschedule_on'] = $tmp_date->format('Y/m/d');
        }
    }

    public function getLastRescheduleOnAttribute()
    {
        return $this->attributes['last_reschedule_on'] ? Carbon::parse($this->attributes['last_reschedule_on'])->format('m/d/Y') : '';
    }

}