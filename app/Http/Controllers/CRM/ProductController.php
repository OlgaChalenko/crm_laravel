<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Imports\ImportProduct;
use App\Models\Currency;
use App\Models\Manufacturer;
use App\Models\ManufacturerInfo;
use App\Models\Product;
use App\Models\ProductUniqueCodes;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use SebastianBergmann\CodeCoverage\StaticAnalysis\ExecutableLinesFindingVisitor;
use Picqer\Barcode\BarcodeGeneratorPNG;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
      $products = Product::join('oc_product_description', 'oc_product.product_id', '=', 'oc_product_description.product_id')
            ->where('oc_product_description.language_id', 1)
            ->select('oc_product.*', 'oc_product_description.name')->paginate(50);
        /*$product = [];
      $product_codes = ProductUniqueCodes::all()->toArray();
        foreach ($product_codes as $product_code) {
            $image_name = str_replace('/','-',$product_code['code']);
            $image_qr = 'catalog/product_qr/'. $image_name.'_qr.svg';
            QrCode::generate(env('MAIN_DOMAIN_PRODUCT_PATH').$product_code['product_id'].'&qr_code=true',env('MAIN_DOMAIN_IMAGE').$image_qr);
            ProductUniqueCodes::where('id', $product_code['id'])
                ->update([
                    'qrcode' => $image_qr
                ]);
        }
        dd( 111);*/
        return view('crm.products.index', compact('products'));
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
