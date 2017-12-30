<?php

namespace MCMIS\Foundation\Traits\Complain;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use FarhanWazir\GoogleMaps\GMaps;

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

	public function getMapMapAttribute()
	{
		$coord = '';
		if ($this->attributes['latitude'] && $this->attributes['longitude'])
			$coord = $this->attributes['latitude'] . ',' . $this->attributes['longitude'];
		elseif ($this->location) { //if coordinates not exists then try with manual address
			/*$coord = (!empty($this->location->street_number) ? $this->location->street_number . ' ' : '') . $this->location->street . ', '
				. $this->location->block . ', ' . $this->location->area . ', '
				. $this->location->city
				. ((config('csys.coverage.type') != 'country') ? ', ' . config('csys.coverage.data.country') : '');*/

			$block = ($this->location->block) ? $this->location->block : ($this->location->street) ? $this->location->street : '';
			$street_number = ($this->location->street_number) ? $this->location->street_number : '';
			$area = ($this->location->area) ? $this->location->area : '';
			$zip_code = ($this->location->zip_code) ? $this->location->zip_code : '03100';
			$coord = 'Calle '.$block.' '.$street_number.', '.$area.', '.$zip_code.' Ciudad de México, CDMX';
			$coord = urlencode($coord);
		}

		$marker = $this->childCategory ? $this->childCategory->static_map_marker : ($this->category ? $this->category->static_map_marker : '');


		$marker2 = array();
		$marker2['draggable'] = false;
		$marker2['position'] = $coord;
		$marker2['icon'] = $marker;
		$config = array();
		$config['map_height'] = '255px';
		$config['zoom'] = 17;
		$config['map_type'] = 'ROADMAP';
		$config['center'] = $coord;
		$config['places'] = true;
		$gmaps = new GMaps($config);
		$gmaps->add_marker($marker2);
		$map = $gmaps->create_map();
		return $map;
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

    public function getRegisteredTimeAttribute()
    {
        return $this->attributes['created_at'] ? Carbon::parse($this->attributes['created_at'])->format('G:m') : '';
    }

    public function getRegisteredOnAttribute()
    {
        return $this->attributes['created_at'] ? Carbon::parse($this->attributes['created_at'])->format('m/d/Y G:m') : '';
    }

}