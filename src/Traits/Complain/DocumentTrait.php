<?php
namespace App\Http\Controllers\Traits\Complain;


use App\ComplaintDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

trait DocumentTrait
{
    public function uploadDocuments(Request $request){
        $all_data = [];
        $path = config('csys.setting.form.upload.document.path');
        File::makeDirectory($path, 0777, true, true);
        $date = date_create();

        foreach($request->documents as $key => $document){
            //upload
            if($document && $document->isValid()){
                $name = $all_data[]['name'] = uniqid($request->complain_no . '_' . date_timestamp_get($date)) . '.' . $document->getClientOriginalExtension();
                if($document->move($path, $name)){
                    if($request->request->has('uri'))
                        $request->request->set('uri', $name);
                    else $request->request->add(['uri' => $name]);
                    $request->request->set('caption', $document->getClientOriginalName());
                    ComplaintDocument::create($request->only(['uri', 'complaint_id', 'user_id', 'caption']));
                }
            }
        }

        return $all_data;
    }

    public function downloadDocument($complain_no, $file){
        $file_path = config('csys.setting.form.upload.document.path');
        if(File::exists($file_path.$file)){
            return response()->download($file_path.$file);
        }

        abort(404, trans('alert.file.found.error', ['file' => $file]));
    }
}
