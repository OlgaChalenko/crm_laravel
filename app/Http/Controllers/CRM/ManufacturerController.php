<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Manufacturer;
use App\Models\ManufacturerInfo;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ManufacturerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $manufacturers = Manufacturer::orderBy('name', 'ASC')->paginate(20);
        return view('crm.manufacturers.index', compact('manufacturers'));
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
        $request->validate([
            'email'=> 'email',
            'logo_new' => 'nullable|image',
        ]);

        $data = $request->all();
        if($request->hasFile('logo_new')){
            $folder = 'brands_logo';
            $data['logo'] = $request->file('logo_new')->store($folder);
        }
        ManufacturerInfo::create($data);
        return redirect()->route('brands.index')->with('success', 'Информация по поставщику обновлена!');
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
        $manufacturer = Manufacturer::find($id);
        $countries = Country::all();
        $manufacturer_info = ManufacturerInfo::where('manufacturer_id', '=', $id)->first();
        if($manufacturer_info == null){
            return view('crm.manufacturers.create', compact('manufacturer', 'countries' ));
        }
        return view('crm.manufacturers.edit', compact('manufacturer_info', 'manufacturer', 'countries' ));
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
        $request->validate([
            'email'=> 'email',
            'logo_new' => 'nullable|image',
        ]);
        $manufacturer_info = ManufacturerInfo::find($id);
        $data = $request->all();

        if($request->hasFile('logo_new')){
            $folder = 'brands_logo';
            $data['logo'] = $request->file('logo_new')->store($folder);
        }
        $manufacturer_info->update($data);
        return redirect()->route('brands.index')->with('success', 'Информация по поставщику обновлена!');
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
