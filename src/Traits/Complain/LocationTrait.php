<?php
namespace MCMIS\Foundation\Traits\Complain;

use FarhanWazir\GoogleMaps\GMaps;
use Illuminate\Support\Facades\Input;

trait LocationTrait
{

    public function map($position = null){
        /* Google Map */

        // set up the marker ready for positioning
        // once we know the users location
        $marker = array();

        //$marker['position'] = '24.929014118727277, 67.13037382607263';
        $marker['animation'] = 'DROP';
        $marker['draggable'] = true;
        $marker['ondragend'] = 'document.getElementById("form-map-latitude").value = event.latLng.lat();
                                document.getElementById("form-map-longitude").value = event.latLng.lng();';


        $config = array();
        $config['map_height'] = '255px';
        $config['zoom'] = 15;
        $config['map_type'] = 'ROADMAP';
        //$config['center'] = '24.929014118727277,67.13037382607263';
        $config['center'] = $position ? $position['latitude'] .','. $position['longitude'] : 'auto';
        $config['onboundschanged'] = 'if (!centreGot) {
            var mapCentre = map.getCenter();
            marker_0.setOptions({
                position: new google.maps.LatLng(mapCentre.lat(), mapCentre.lng())
            });
        }
        centreGot = true;
        ';

        $config['onclick'] = 'marker_0.setPosition(event.latLng); '. $marker['ondragend'];

        $config['places'] = true;
        $config['placesAutocompleteInputID'] = 'map-place-search';
        $config['placesAutocompleteOnChange'] = ' var autocomplete = placesAutocomplete;
        var place = autocomplete.getPlace();
          if (!place.geometry) {
            alert(\'Place contains no geometry, search another.\');
            return;
          }else{
              if (place.geometry.viewport) {
                map.fitBounds(place.geometry.viewport);
              } else {
                map.setCenter(place.geometry.location);
                map.setZoom('.$config['zoom'].');
              }
              marker_0.setPosition(place.geometry.location);
              event = {latLng: place.geometry.location}; '.$marker['ondragend']
            .'} ';

        $gmaps = new GMaps($config);


        $gmaps->add_marker($marker);

        $map = $gmaps->create_map();
        /* End Google Map*/

        return $map;
    }

    public function mapField($position = false){
        return view('acciones.complain.partial.form.location_map', [
            'position' => $position,
        ]);
    }

    public function manualLocationField($default = false){
        $areas = sys('model.location.area')->all();
        $areas_list = ['' => 'Ninguna'];
        foreach($areas as $area){
            $areas_list[$area->id] = $area->name;
        }


        $preset_locations = sys('model.location.preset')->all();
        $preset_location_list = ['' => 'Ninguna'];
        foreach($preset_locations as $preset_location){
            $preset_location_list[$preset_location->id] = $preset_location->title;
        }

        return view('acciones.complain.partial.form.location_address', [
            'preset_locations' => $preset_location_list,
            'areas' => $areas_list,
            'blocks' => [],
            'streets' => [],
            'city' => 'Ciudad de México, CDMX',
        ]);
    }

    public function manualPresetLocationField($default = false){
        $item = sys('model.location.preset')->findOrFail($default);

        return view('acciones.complain.partial.form.location_park', [
            'item' => $item,
        ]);
    }

    public function getLatlngFromGoogle($q = false){
        $q = !$q ? Input::get('address') : $q;

        $address_search = '';

        if (isset($q['otra_calle']) && $q['otra_calle'] != '') {
        	$address_search = $q['otra_calle'] . ', ';
			if (isset($q['street_number'])) {
				$address_search .= $q['street_number'] . ' ';
			}

			if (isset($q['area_id'])) {
				$area = sys('model.location.area')->findOrFail($q['area_id']);

				$address_search .= $area->name . ', ';
			}

			$address_search .= '03100 Ciudad de México, CDMX ';
			/*$address_search = (!empty($q['street_number']) ? $q['street_number'] . ' ' : '') . $q['otra_calle'] . ', ';
			$address_search .= config('csys.coverage.data.' . config('csys.coverage.type'))
				. ((config('csys.coverage.type') != 'country') ? ', ' . config('csys.coverage.data.country') : '');*/
		} else {

			if (isset($q['block_id'])) {
				$block = sys('model.location.block')->findOrFail($q['block_id']);

				$address_search = 'Calle  ';
				$address_search .= $block->name . ', ';
			} else if (isset($q['street_id'])) {
				$street = sys('model.location.street')->findOrFail($q['street_id']);

				$address_search = 'Calle  ';
				$address_search .= $street->name . ', ';
			}

			if (isset($q['street_number'])) {
				$address_search .= $q['street_number'] . ' ';
			}

			if (isset($q['area_id'])) {
				$area = sys('model.location.area')->findOrFail($q['area_id']);

				$address_search .= $area->name . ', ';
			}

			$address_search .= '03100 Ciudad de México, CDMX ';
		}

		/*$address_search .= config('csys.coverage.data.' . config('csys.coverage.type'))
			. ((config('csys.coverage.type') != 'country') ? ', ' . config('csys.coverage.data.country') : '');*/

        /*if (isset($q['street_id_another']) && $q['street_id_another'] != '') {
        	$address_search = $this->createAddressFromStreetID($q['street_id_another'], (isset($q['street_number'])? $q['street_number'] : ''));
		} else {
			$address_search = (!empty($q['street_number']) ? $q['street_number'] . ' ' : '') . $q['otra_calle'] . ', '
				. config('csys.coverage.data.' . config('csys.coverage.type'))
				. ((config('csys.coverage.type') != 'country') ? ', ' . config('csys.coverage.data.country') : '');;
		}*/

        $req = sys('Curl')->to('https://maps.googleapis.com/maps/api/geocode/json')
            ->withData([
                'language' => config('csys.lang'),
                'address' => $address_search,
				'key' => 'AIzaSyDq50Ber0w5W8NJ_B3sReEN-VinvKDinOw'
            ])
            ->withOption('SSL_VERIFYPEER', false)
            ->asJson()
            ->get();
        if($req && $req->status == "OK") return response()->json($req->results[0]->geometry->location);
        return false;
    }

    public function createAddressFromStreetID($id, $number)
    {
        $street = sys('model.location.street')->findOrFail($id);
        return (!empty($number) ? $number . ' ' : '') . $street->name . ', '
            . $street->block->name . ', ' . $street->block->area->name . ', '
            . config('csys.coverage.data.' . config('csys.coverage.type'))
            . ((config('csys.coverage.type') != 'country') ? ', ' . config('csys.coverage.data.country') : '');
    }

    public function getAddressFromGoogle($q = false){
        $q = !$q ? Input::get('latlng') : $q;

        $req = sys('Curl')->to('https://maps.googleapis.com/maps/api/geocode/json')
            ->withData([
                'language' => config('csys.lang'),
                'latlng' => $q,
				'key' => 'AIzaSyDq50Ber0w5W8NJ_B3sReEN-VinvKDinOw'
            ])
            ->withOption('SSL_VERIFYPEER', false)
            ->asJson()
            ->get();
        if($req && $req->status == "OK") return response()->json($req->results[0]->formatted_address);
        return false;
    }

	public function getZipCodeFromGoogle($q = false){
		$q = !$q ? Input::get('latlng') : $q;

		$req = sys('Curl')->to('https://maps.googleapis.com/maps/api/geocode/json')
			->withData([
				'language' => config('csys.lang'),
				'latlng' => $q,
				'key' => 'AIzaSyDq50Ber0w5W8NJ_B3sReEN-VinvKDinOw'
			])
			->withOption('SSL_VERIFYPEER', false)
			->asJson()
			->get();
		if($req && $req->status == "OK"){
			$zip = '03100';
			$zip_code = $req->results[0]->address_components;

			for ($i=0; $i<count($zip_code); $i++) {
				if ($zip_code[$i]->types[0] == 'postal_code') {
					$zip = $zip_code[$i]->long_name;
					break;
				}
			}

			return response()->json($zip);
		}
		return false;
	}

}
