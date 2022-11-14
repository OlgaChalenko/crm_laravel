<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Models\Option;
use App\Models\OptionValueDescription;
use App\Models\Product;
use Illuminate\Http\Request;

class OptionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $options_all = Option::leftJoin('oc_option_description', 'oc_option.option_id', '=', 'oc_option_description.option_id')
            ->leftJoin('oc_manufacturer', 'oc_option.manufacturer_id', '=', 'oc_manufacturer.manufacturer_id')
            ->select('oc_manufacturer.name as brand', 'oc_option.*', 'oc_option_description.*')
            ->orderBy('oc_manufacturer.name', 'ASC')
            ->orderBy('oc_option_description.name', 'ASC')
            ->where('oc_option_description.language_id', 2)->get();
        $options = [];
        if($options_all){
            foreach ($options_all as $option) {
                $option_value_all = OptionValueDescription::where('option_id',$option['option_id'])
                    ->where('language_id', 2)
                    ->orderBy('name', 'ASC')
                    ->select('name')->get()->toArray();
                $option_values = [];
                if($option_value_all){
                    foreach ($option_value_all as $item) {
                        $option_values[] = $item['name'];
                    }
                }
                $options[$option['brand']][$option['type_product']][] = [
                    'name' => $option['name'],
                    'href' => env('MAIN_DOMAIN').'admin/index.php?route=catalog/option/edit&option_id='.$option['option_id']."&admin_crm_token=".hash('md5',env('MAIN_DOMAIN_KEY')),
                    'values' => implode(', ', $option_values),
                    ];
            }
        }
        return view('crm.options.index', compact('options', ));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
