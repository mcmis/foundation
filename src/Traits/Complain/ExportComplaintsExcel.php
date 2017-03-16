<?php

namespace MCMIS\Foundation\Traits\Complain;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

trait ExportComplaintsExcel
{

    public function exportComplaintsList(Request $request, $method, $status = null, $department = null){

        if($method == 'index')
            $data = $this->{$method}($request, false, true);
        elseif($method != 'get')
            $data = $this->{$method}($request, $department, false, true);
        else
            $data = $this->{$method}($request, $status, $department, false, true);

        $data = $data->leftJoin('complain_categories', function($join){
            $join->on(DB::raw('IF(complaint.child_category_id = 0, complaint.category_id, complaint.child_category_id)'), '=', 'complain_categories.id');
        });

        $rows = $data->select('complain_no', 'complain_categories.title as category',
            DB::raw('IFNULL((select CONCAT(complaint_location.street_number, ", ", complaint_location.street, ", ", complaint_location.block, ", ", complaint_location.area) from complaint_location where complaint_location.complaint_id = complaint.id), "") as address'),
            'complaint.created_at as created')->get()->toArray();

        Excel::create("Complaints-$status-$department", function($excel) use ($rows) {

            $excel->setTitle('Complaints list');

            $excel->setCreator('Farhan Wazir')
                ->setCompany('Creative Ideator');

            $excel->setDescription('File generate by Creative ideator (http://cideator.com).');

            $excel->sheet('Complaints', function($sheet) use ($rows) {
                $sheet->fromArray($rows);
            });

        })->download('xls');
    }

}