<?php

namespace App\Exports;

use App\Models\Manufacturer;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;

class ExportProduct implements FromCollection
{
    public $manufacturers = [];
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        if($this->manufacturers){
            $products = Product::leftJoin('oc_product_description', 'oc_product_description.product_id', '=', 'oc_product.product_id')->
                where('oc_product_description.language_id', 1)->
                whereIn('oc_product.manufacturer_id',$this->manufacturers)->
                orderBy('oc_product.model', 'ASC')->
                get()->toArray();
        }else{
            $products = Product::leftJoin('oc_product_description', 'oc_product_description.product_id', '=', 'oc_product.product_id')->
                where('oc_product_description.language_id', 1)->
                orderBy('oc_product.model', 'ASC')->
                get()->toArray();
        }
        $manufacturers = Manufacturer::pluck('name', 'manufacturer_id')->toArray();

        $data_export[] = [
            'Поставщик',
            'Код поставщика',
            'Код',
            'Цвет',
            'Размер',
            'Полнота',
            'Каблук',
            'Закупочная цена',
            'Розничная цена',
            'Код валюты',
            'Остаток'
        ];
        foreach ($products as $product) {
            if(!$product['model']) continue;

            $manufacturer_id = $product['manufacturer_id'];
            $manufacturer_name = $manufacturers[$manufacturer_id] ?? '';
            $model = $product['general_code'] ? $product['general_code'] : $product['model'];
            $color =  $product['color'] ? $product['color'] : '';
            $product_id = $product['product_id'];

            if($color){
                if($product['is_clone']){
                    $main_product = DB::select("SELECT *  FROM `oc_product` WHERE model ='{$product['general_code']}' AND is_clone!=1");
                    $product_id = $main_product[0]->product_id;
                }
                $query_poc = DB::select("SELECT *  FROM `oc_product_option_pro_detail` popd LEFT JOIN oc_option_value_description ovd ON (popd.option_value_id=ovd.option_value_id) WHERE popd.`product_id` = {$product_id} AND ovd.name='{$color}' AND ovd.language_id=1");
                if(count($query_poc)>0){
                    foreach ($query_poc as $poc) {
                        $product_option_pro_value_id = $poc->product_option_pro_value_id;
                        $option_color_value_id = $poc->option_value_id;
                        $query_popv = DB::select("SELECT *  FROM `oc_product_option_pro_value`  WHERE `product_id` = {$product_id} AND product_option_pro_value_id={$product_option_pro_value_id} LIMIT 1 ");
                        $query_popp = DB::select("SELECT *  FROM `oc_product_option_pro_price`  WHERE `product_id` = {$product_id} AND product_option_pro_value_id={$product_option_pro_value_id} AND customer_group_id=5 LIMIT 1 ");
                        $query_pos = DB::select("SELECT *  FROM `oc_product_option_pro_detail` popd LEFT JOIN oc_option_value_description ovd ON (popd.option_value_id=ovd.option_value_id) WHERE popd.`product_option_pro_value_id` = {$product_option_pro_value_id} AND ovd.language_id=1 AND popd.option_value_id!={$option_color_value_id}");
                        foreach ($query_pos as $pos) {
                            $data_export[] = [
                                'manufacturer_name'=> $manufacturer_name,
                                'model' => $model,
                                'code' => $product['model'],
                                'color' => $color,
                                'size' => $pos->name,
                                'heel' => '',
                                'wide' => '',
                                'base_price' => '',
                                'price' => $query_popp[0]->price ?? '',
                                'currency' => 'UAH',
                                'quantity' => $query_popv[0]->quantity ?? '',
                            ];
                        }
                    }
                }
                //SELECT *  FROM `oc_product_option_pro_detail` popd LEFT JOIN oc_option_value_description ovd ON (popd.option_value_id=ovd.option_value_id) WHERE popd.`product_id` = 2979 AND popd.option_id=7 AND ovd.name="Black"
                //dd($data_export);
            }else{
                $query_pop2p = DB::select("SELECT *  FROM `oc_product_option_pro_to_product` WHERE `product_id` = {$product_id} AND product_option_pro_id=4 ");
                if(count($query_pop2p)>0){
                    //Это обувь
                    $query_poc = DB::select("SELECT *  FROM `oc_product_option_pro_detail` popd LEFT JOIN oc_option_value_description ovd ON (popd.option_value_id=ovd.option_value_id) WHERE popd.`product_id` = {$product_id} AND ovd.language_id=1 ORDER BY ovd.option_id ASC");
                    $product_option_pro = [];
                    if(count($query_poc)>0){
                        foreach ($query_poc as $item) {
                            $query_popp = DB::select("SELECT *  FROM `oc_product_option_pro_price`  WHERE `product_id` = {$product_id} AND product_option_pro_value_id={$item->product_option_pro_value_id} AND customer_group_id=5 LIMIT 1 ");
                            $query_popv = DB::select("SELECT *  FROM `oc_product_option_pro_value`  WHERE `product_id` = {$product_id} AND product_option_pro_value_id={$item->product_option_pro_value_id} LIMIT 1 ");
                            $product_option_pro[$item->product_option_pro_value_id]['options'][] = $item->name;
                            $product_option_pro[$item->product_option_pro_value_id]['quantity'] = $query_popv[0]->quantity ?? '';
                            $product_option_pro[$item->product_option_pro_value_id]['price'] = $query_popp[0]->price ?? '';
                        }
                    }
                    if($product_option_pro){
                        foreach ($product_option_pro as $item) {
                            $data_export[] = [
                                'manufacturer_name'=> $manufacturer_name,
                                'model' => $model,
                                'code' => $product['model'],
                                'color' => $item['options'][1] ?? '',
                                'size' => $item['options'][0] ?? '',
                                'heel' => isset($item['options'][3]) ? html_entity_decode($item['options'][3]) : '',
                                'wide' => $item['options'][2] ?? '',
                                'base_price' => '',
                                'price' => $item['price'],
                                'currency' => 'UAH',
                                'quantity' => $item['quantity'],
                            ];
                        }
                    }
                }else{
                    $query_pov = DB::select("SELECT *  FROM `oc_product_option_value` pov LEFT JOIN oc_option_value_description ovd ON (pov.option_value_id=ovd.option_value_id) WHERE pov.`product_id` = {$product_id} AND ovd.language_id=1 ORDER BY ovd.option_id ASC");
                    $product_option = [];
                    if(count($query_pov)>0){
                        foreach ($query_pov as $item) {
                            $product_option[$item->option_id][$item->option_value_id] = [
                                'name' => html_entity_decode($item->name),
                                'quantity' => $item->quantity,
                                'price' => $item->price,
                            ];
                        }
                    }
                    if(count($product_option) > 0 ){
                        if(count($product_option) == 1 ) {
                            foreach ($product_option as $option_id => $options) {
                                foreach ($options as $option) {
                                    $data_export[] = [
                                        'manufacturer_name' => $manufacturer_name,
                                        'model' => $model,
                                        'code' => $product['model'],
                                        'color' => $option_id == 7 ? $option['name'] : '',
                                        'size' => $option_id == 6 ? $option['name'] : '',
                                        'heel' => $option_id == 9 ? $option['name'] : '',
                                        'wide' => $option_id == 8 ? $option['name'] : '',
                                        'base_price' => '',
                                        'price' => $option['price'],
                                        'currency' => 'UAH',
                                        'quantity' => $option['quantity'],
                                    ];
                                }
                            }
                        }else{
                            dd($product);
                        }
                    }else{
                        $data_export[] = [
                            'manufacturer_name' => $manufacturer_name,
                            'model' => $model,
                            'code' => $product['model'],
                            'color' =>  '',
                            'size' =>  '',
                            'heel' =>  '',
                            'wide' =>  '',
                            'base_price' => '',
                            'price' => $product['price'],
                            'currency' => 'UAH',
                            'quantity' => $product['quantity'],
                        ];
                    }
                }
            }
           /* $data_export[] = [
                'manufacturer_name'=> $manufacturer_name,
                'model' => $model,
                'code' => $product['model'],
                'color' => $color,
            ];*/

        }
        return  collect($data_export);
        //dd($products);
      //  return Product::all();
    }
}
