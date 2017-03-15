<?php
namespace App\Http\Controllers\Traits\Complain;


use App\ComplaintPhotos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;

trait PhotoTrait
{

    public function uploadPhotos(Request $request){
        /* TODO: on update old file will overwrite with same name */
        $all_data = [];
        $path = config('csys.setting.form.upload.photo.path');
        File::makeDirectory($path, 0777, true, true);
        $date = date_create();
        foreach($request->photos as $key => $photo){
            //upload
            if($photo && $photo->isValid()){
                $name = $all_data[]['name'] = uniqid($request->complain_no . '_' . date_timestamp_get($date)) . '.' . $photo->getClientOriginalExtension();
                $item = Image::make($photo->getRealPath());
                if($item->save($path . $name)){
                    if($request->request->has('uri'))
                        $request->request->set('uri', $name);
                    else $request->request->add(['uri' => $name]);
                    ComplaintPhotos::create($request->only(['uri', 'complaint_id', 'user_id', 'caption']));
                }
            }
        }

        return $all_data;
    }

}