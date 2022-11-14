<?php

namespace App\Http\Controllers\CRM;

use App\Exports\ExportProduct;
use App\Http\Controllers\Controller;
use App\Models\Manufacturer;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends Controller
{
    public function index()
    {
        $manufacturers_all = Manufacturer::orderBy('name', 'ASC')->get()->toArray();
        $manufacturers = [];
        foreach ($manufacturers_all as $manufacturer) {
            $manufacturers[$manufacturer['manufacturer_id']] = $manufacturer['name'];
        }
        return view('crm.export.index', compact('manufacturers'));
    }


    public function export(Request $request)
    {
        $name_file = 'all_';
        $export = new ExportProduct();
        if(isset($request['manufacturer_ids'])){
            $export->manufacturers = $request['manufacturer_ids'];
            $manufacturers = Manufacturer::pluck('name', 'manufacturer_id')->toArray();
            $name_file = '';
            foreach($request['manufacturer_ids'] as $manufacturer_id) {
                $name_file .= $manufacturers[$manufacturer_id].'_';
            }
        }
        $name_file .= date("Y-m-d_H:i:s");
        return Excel::download($export, $name_file.'.xlsx');
    }
}
