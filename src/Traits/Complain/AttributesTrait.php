<?php

namespace MCMIS\Foundation\Traits\Complain;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

trait AttributesTrait
{

    public function getMapImageAttribute()
    {
        $coord = '';
        if ($this->attributes['latitude'] && $this->attributes['longitude'])
            $coord = $this->attributes['latitude'] . ',' . $this->attributes['longitude'];
        elseif ($this->location) //if coordinates not exists then try with manual address
            $coord = (!empty($this->location->street_number) ? $this->location->street_number . ' ' : '') . $this->location->street . ', '
                . $this->location->block . ', ' . $this->location->area . ', '
                . $this->location->city
                . ((config('csys.coverage.type') != 'country') ? ', ' . config('csys.coverage.data.country') : '');
        $marker = $this->childCategory ? $this->childCategory->static_map_marker : ($this->category ? $this->category->static_map_marker : '');
        return '<img src="https://maps.googleapis.com/maps/api/staticmap?key=' . config('csys.googlemaps.static_key') . '&center=' . $coord . '&markers=icon: ' . $marker . '|' . $coord . '&zoom=17&size=300x300&sensor=false" class="complaint-list-map-image" />';
    }

    public function setExpectedCompletedOnAttribute($value)
    {
        if (!empty($value)) {
            $tmp_date = Carbon::createFromFormat('m/d/Y', $value);
            $this->attributes['expected_completed_on'] = $tmp_date->format('Y/m/d');
        } else $this->attributes['expected_completed_on'] = null;
    }

    public function getExpectedCompletedOnAttribute()
    {
        return $this->attributes['expected_completed_on'] ? Carbon::parse($this->attributes['expected_completed_on'])->format('m/d/Y') : '';
    }

    public function getUrlAttribute()
    {
        return action('ComplainsController@show', ['complain_no' => $this->attributes['complain_no']]);
    }

    public function setCompletedOnAttribute($value)
    {
        if (!empty($value)) {
            $tmp_date = Carbon::createFromFormat('m/d/Y', $value);
            $this->attributes['completed_on'] = $tmp_date->format('Y/m/d');
        } else $this->attributes['completed_on'] = null;
    }

    public function getCompletedOnAttribute()
    {
        return $this->attributes['completed_on'] ? Carbon::parse($this->attributes['completed_on'])->format('m/d/Y') : '';
    }

    public function setRescheduleOnAttribute($value)
    {
        if (!empty($value)) {
            $tmp_date = Carbon::createFromFormat('m/d/Y', $value);
            $this->attributes['reschedule_on'] = $tmp_date->format('Y/m/d');
        } else $this->attributes['reschedule_on'] = null;
    }

    public function getRescheduleOnAttribute()
    {
        return $this->attributes['reschedule_on'] ? Carbon::parse($this->attributes['reschedule_on'])->format('m/d/Y') : '';
    }

}