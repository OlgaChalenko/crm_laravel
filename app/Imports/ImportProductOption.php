<?php

namespace App\Imports;

use App\Models\Currency;
use App\Models\Manufacturer;
use App\Models\ManufactuterDescription;
use App\Models\Option;
use App\Models\OptionDescription;
use App\Models\OptionValue;
use App\Models\OptionValueDescription;
use App\Models\Product;
use App\Models\Product2Store;
use App\Models\ProductDescription;
use App\Models\ProductOption;
use App\Models\ProductOptionPro;
use App\Models\ProductOptionPro2Product;
use App\Models\ProductOptionProDescription;
use App\Models\ProductOptionProDetail;
use App\Models\ProductOptionProKit;
use App\Models\ProductOptionProPrice;
use App\Models\ProductOptionProValue;
use App\Models\ProductOptionProWarehouse;
use App\Models\ProductOptionValue;
use App\Models\ProductOptionWarehouse;
use App\Models\ProductUniqueCodes;
use App\Models\ProductWarehouse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Picqer\Barcode\BarcodeGeneratorPNG;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ImportProductOption implements ToModel
{
    public $manufacturer_id = 0;
    public $is_shoes = false;
    public $has_option = false;
    public $manufacturers = [];
    public $manufacturer_options = [];
    public $currencies = [];
    public $languages = [];
    public $customer_groups = [];
    public $config_languge_id = 1;
    public $config_customer_group_id = 1;
    public $option_pro_id = 4;
    public $warehouse_id = 1;
    public $update_product_option = [];
    public $update_product_option_pro = [];

    private $main_options = [
        0 => [
            'name' => 'Size',
            'names' => [
                1 => 'Size', //en
                2 => 'Размер', //ru
                3 => 'Розмір', //uk
                4 => 'Velikost', //cz
                5 => 'Größe', //de
            ],
        ],
        1 => [
            'name' => 'Color',
            'names' => [
                1 => 'Color', //en
                2 => 'Цвет', //ru
                3 => 'Колір', //uk
                4 => 'Barva', //cz
                5 => 'Farbe', //de
            ],
        ],
        2 => [
            'name' => 'Wide',
            'names' => [
                1 => 'Wide', //en
                2 => 'Полнота', //ru
                3 => 'Повнота', //uk
                4 => 'Široký', //cz
                5 => 'Breit', //de
            ],
        ],
        3 => [
            'name' => 'Heel',
            'names' => [
                1 => 'Heel', //en
                2 => 'Каблук', //ru
                3 => 'Каблук', //uk
                4 => 'Pata', //cz
                5 => 'Hacke', //de
            ],
        ],
    ];

    public $currency_id = 0;
    public $full_import = false;
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */

    public function model(array $row)
    {
        if($row[1] == ''){
            return;
        }
        if($row[0] == 'Производитель' || $row[0] == 'Поставщик'){
            return;
        }
        $this->is_shoes = false;
        if(isset($row[5]) && $row[5]){
            //Текущая позиция - обувь
            $this->is_shoes = true;
        }
        $this->has_option = false;
        if(isset($row[4]) && $row[4]){
            //Текущая позиция - обувь
            $this->has_option = true;
        }
        $manufacturer_name = trim($row[0]); // Название поставщика
        $code_manufacturer = htmlspecialchars(trim($row[1])); // Код поставщика
        $code = htmlspecialchars(trim($row[2])); // Код внутренний
        $color = htmlspecialchars(trim($row[3])); // Цвет
        $size = htmlspecialchars(trim($row[4])); // Размер
        $heel = htmlspecialchars(trim($row[5])); // Каблук
        $wide = htmlspecialchars(trim($row[6])); // Полнота
        $base_price = trim($row[7]); // Закупочная цена
        $price = trim($row[8]); // Закупочная цена
        $currency_code = trim($row[9]); // Код валюты
        $currency_id = $this->currencies[trim($row[9])] ?? 3; // Код валюты
        $quantity = trim($row[10]); // Остаток

        if(!trim($manufacturer_name)){
            return;
        }
        if(!isset($this->manufacturers[$manufacturer_name])){
            $manufacturer_id = $this->addManufacturer($manufacturer_name);
            $this->manufacturers[$manufacturer_name] = $manufacturer_id;
        }else $manufacturer_id = $this->manufacturers[$manufacturer_name];

        $res = $this->getManufacturerOptions($manufacturer_id);
        if(!$res)
            return;

        $type_product = $this->is_shoes ? 'shoes' : 'other';
        $size_option_id = $this->manufacturer_options[$manufacturer_id][$type_product][0] ?? 0;
        $color_option_id = $this->manufacturer_options[$manufacturer_id][$type_product][1] ?? 0;
        $heel_option_id = $this->manufacturer_options[$manufacturer_id][$type_product][2] ?? 0;
        $wide_option_id = $this->manufacturer_options[$manufacturer_id][$type_product][3] ?? 0;
        $option_pro_id = $this->manufacturer_options[$manufacturer_id]['option_pro_id'] ?? $this->option_pro_id;

        $data_option = [
            'quantity' => $quantity,
            'base_price' => $base_price,
            'price' => $price,
            'currency_code' => $currency_code,
            'currency_id' => $currency_id,
            'model' => $code,
            'color' => $color,
            'code_manufacturer' => $code_manufacturer,
            'manufacturer_id' => $manufacturer_id,
        ];

        $product = Product::where('model', $code)->first();

        if(!$product){
            Log::channel('import_update')->error("Товар с внутренним кодом {$code} не найден в системе!");
            $product_id = $this->addProduct($data_option);
        }else {
            $product = $product->toArray();
            $product_id = $product['product_id'];
            $product_description = ProductDescription::where('product_id', $product_id)->get()->toArray();
            foreach ($product_description  as $item) {
                if($item['color']){
                    continue;
                }
                ProductDescription::where('product_id', $product_id)
                    ->where('language_id', $item['language_id'])
                    ->update([
                        'color' => $color,
                    ]);
           }
        }

        if(!$this->is_shoes){
            //Обрабатываем НЕ обувь
            if(!$this->has_option){
                //Товар не имеет опций
                ProductOptionValue::where('product_id', $product_id)->delete();
                $quantity = (int) $quantity;
                Product::where('product_id', $product_id)
                    ->update([
                        'quantity' => DB::raw("quantity+{$quantity}"),
                        'price' => $price,
                        'currency_id' => $currency_id,
                        'base_price' => $base_price
                    ]);
                $this->generateProductUniqueCodes($product_id, 0,0,$quantity);
            }else {
                if (!isset($this->update_product_option[$product_id])) {
                    $this->update_product_option[$product_id] = $product_id;
                }
                $this->updateProductOption($product_id, $size_option_id, $size, $data_option);
            }
        }else{
            //Обрабатываем обувь
            $related_option = [
                $size_option_id => $size,
                $color_option_id => $color,
                $heel_option_id => $heel,
                $wide_option_id => $wide,
            ];
            if(!isset($this->update_product_option_pro[$product_id])){
                $this->update_product_option_pro[$product_id] = $product_id;
                ProductOptionValue::where('product_id', $product_id)
                    ->update([
                        //'quantity' => 0,
                        'price' => 0
                    ]);
               /* ProductOptionProValue::where('product_id', $product_id)
                    ->update([
                        'quantity' => 0
                    ]);*/
            }
            $this->updateProductRelatedOption($product_id, $option_pro_id, $related_option, $data_option);
        }
    }

    private function updateProductRelatedOption($product_id, $option_pro_id, $related_option, $data_option){
        $product_option_pro_to_product = ProductOptionPro2Product::where('product_id', $product_id)->
            where('product_option_pro_id',  $option_pro_id)->first();
        if(!$product_option_pro_to_product){
            ProductOptionPro2Product::where('product_id', $product_id)->delete();
            ProductOptionPro2Product::create([
                'product_id' => $product_id,
                'product_option_pro_id' => $option_pro_id,
            ]);
        }

        $related_option_value_ids = [];
        foreach ($related_option as $option_id => $option_value_name) {
            $related_option_value_ids[$option_id] = $this->getOptionValueId($option_id, $option_value_name);
            $this->updateProductOption($product_id, $option_id, $option_value_name, $data_option, true);
        }

        $product_option_pro_detail = ProductOptionProDetail::where('product_id', $product_id)->get()->toArray();

        if(!$product_option_pro_detail){
            $product_option_pro_value_id = $this->insertProductOptionProValue($product_id, $data_option);
            $this->insertProductOptionProDetail($product_id, $product_option_pro_value_id, $related_option_value_ids);
            $this->insertProductOptionProPrice($product_id, $product_option_pro_value_id, $data_option);
           ProductOptionProWarehouse::create([
                'product_id' => $product_id,
                'related_option_id' => $product_option_pro_value_id,
                'warehouse_id' => $this->warehouse_id,
                'quantity' => $data_option['quantity'] ?? 0
            ]);
            $this->generateProductUniqueCodes($product_id, 0,$product_option_pro_value_id, $data_option['quantity']);
            return true;
        }

        $related_product_options = [];
        foreach ($product_option_pro_detail as $option_pro_detail){
            $related_product_options[$option_pro_detail['product_option_pro_value_id']][] = $option_pro_detail['option_value_id'];
        }

        foreach ($related_product_options as $option_pro_value_id => $option_values) {
            $res = array_diff($option_values, $related_option_value_ids);
            if(!$res){
                $product_option_pro_value_id = $option_pro_value_id;
                $quantity = $data_option['quantity'] ?? 0;
                ProductOptionProPrice::where('product_option_pro_value_id', $product_option_pro_value_id)->delete();
                $this->insertProductOptionProPrice($product_id, $product_option_pro_value_id, $data_option);
                $product_option_pro_value = ProductOptionProValue::where('product_option_pro_value_id', $product_option_pro_value_id)->first()->toArray();
                ProductOptionProValue::where('product_option_pro_value_id', $product_option_pro_value_id)->delete();
                $quantity = $quantity + $product_option_pro_value['quantity'];
                $quantity_new_option =  $data_option['quantity'];
                $data_option['quantity'] = $quantity;
                $this->insertProductOptionProValue($product_id, $data_option, $product_option_pro_value_id);
                ProductOptionProWarehouse::where('related_option_id',$product_option_pro_value_id)
                    ->where('product_id',$product_id)->delete();
                ProductOptionProWarehouse::create([
                    'product_id' => $product_id,
                    'related_option_id' => $product_option_pro_value_id,
                    'warehouse_id' => $this->warehouse_id,
                    'quantity' => $data_option['quantity']
                ]);
                $this->generateProductUniqueCodes($product_id, 0,$product_option_pro_value_id, $quantity_new_option);
                return true;
            }
        }

        $product_option_pro_value_id = $this->insertProductOptionProValue($product_id, $data_option);
        $this->insertProductOptionProDetail($product_id, $product_option_pro_value_id, $related_option_value_ids);
        $this->insertProductOptionProPrice($product_id, $product_option_pro_value_id, $data_option);
        ProductOptionProWarehouse::where('related_option_id',$product_option_pro_value_id)
            ->where('product_id',$product_id)->delete();
        ProductOptionProWarehouse::create([
            'product_id' => $product_id,
            'related_option_id' => $product_option_pro_value_id,
            'warehouse_id' => $this->warehouse_id,
            'quantity' => $data_option['quantity'] ?? 0
        ]);

        $this->generateProductUniqueCodes($product_id, 0,$product_option_pro_value_id, $data_option['quantity']);
        return true;


    }

    private function insertProductOptionProDetail($product_id, $product_option_pro_value_id, $options){
        $data_product_option_pro_detail = [];
        foreach ($options as $option_id => $option_value_id ) {
            $data_product_option_pro_detail[] = [
                'product_option_pro_value_id' => $product_option_pro_value_id,
                'product_id'=> $product_id,
                'option_id'=> $option_id,
                'option_value_id'=> $option_value_id,
            ];
        }
        ProductOptionProDetail::insert($data_product_option_pro_detail);
    }

    private function insertProductOptionProPrice($product_id, $product_option_pro_value_id, $data_option){

        $data_product_option_pro_price = [];
        foreach ($this->customer_groups as $customer_group_id) {
            $data_product_option_pro_price[] = [
                'product_option_pro_value_id' => $product_option_pro_value_id,
                'product_id' => $product_id,
                'customer_group_id' => $customer_group_id,
                'price' => $data_option['price'],
                'base_price' => $data_option['base_price'] ?? 0,
                'currency_id' => $data_option['currency_id'],
            ];
        }
        ProductOptionProPrice::insert($data_product_option_pro_price);
    }

    private function insertProductOptionProValue($product_id, $data_option, $product_option_pro_value_id = 0){
        $data_product_option_pro_value = [
            'product_id' => $product_id,
            'quantity' => $data_option['quantity'] ?? 0,
            'sku' => $data_option['model'],
            'model' => '',
        ];
        if($product_option_pro_value_id){
            $data_product_option_pro_value['product_option_pro_value_id'] = $product_option_pro_value_id;
        }
        $product_option_pro_value_id = ProductOptionProValue::insertGetId($data_product_option_pro_value);
        return $product_option_pro_value_id;
    }

    private function updateProductOption($product_id, $option_id, $size, $data, $related_option = false){
        $product_option_size = ProductOption::where('product_id', $product_id)
            ->where('option_id', $option_id)->first();
        if(!$product_option_size){
            $data_product_option = [
                'product_id' => $product_id,
                'option_id' => $option_id,
                'value'=> '',
                'required'=> 1,
            ];
            $product_option_size = ProductOption::create($data_product_option);
            $product_option_id = $product_option_size->product_option_id;
        }else $product_option_id = $product_option_size->product_option_id;

       $option_value_id = $this->getOptionValueId($option_id, $size);

        $product_option_value = ProductOptionValue::where('product_id', $product_id)
            ->where('product_option_id', $product_option_id)
            ->where('option_id', $option_id)
            ->where('option_value_id', $option_value_id)->first();
        if($product_option_value){
            $quantity = $data['quantity'] ? $data['quantity'] : 0;

            if($related_option){
                $data_product_option_value = [
                    'quantity' => DB::raw("quantity+{$quantity}"),
                    'base_price' => 0,
                    'price' => 0,
                    'currency_id' => $data['currency_id'],
                ];
            }else{
                $data_product_option_value = [
                    'quantity' => DB::raw("quantity+{$quantity}"),
                    'base_price' => $data['base_price'],
                    'price' => $data['price'] ?? 0,
                    'currency_id' => $data['currency_id'],
                ];
                ProductOptionWarehouse::where('product_option_value_id', $product_option_value->product_option_value_id)
                    ->update([ 'quantity' => DB::raw("quantity + {$quantity}")]);
            }

            $product_option_value->update($data_product_option_value);

            if(!$related_option){
                $this->generateProductUniqueCodes($product_id, $product_option_value->product_option_value_id,0,$quantity);
            }
            return true;
        }
        $data_product_option_value = [
            'product_option_id' =>  $product_option_id,
            'product_id' => $product_id,
            'option_id' => $option_id,
            'option_value_id' => $option_value_id,
            'quantity' => $data['quantity'] ?? 0,
            'subtract' => 1,
            'base_price' => $data['base_price'],
            'price' => 0,
            'currency_id' => $data['currency_id'],
            'price_prefix' => '+',
            'points' => '0',
            'points_prefix' => '=',
            'weight' => '0',
            'weight_prefix' => '=',
            'upc' => '',
        ];
        if(!$related_option){
            $data_product_option_value['price'] = $data['price'];
        }
        $product_option_value = ProductOptionValue::create($data_product_option_value);
        if(!$related_option) {
            ProductOptionWarehouse::create([
                'product_id' => $product_id,
                'product_option_value_id' => $product_option_value->product_option_value_id,
                'warehouse_id' => $this->warehouse_id,
                'quantity' => $data['quantity'] ?? 0,
            ]);
        }
        if(!$related_option){
            $this->generateProductUniqueCodes($product_id, $product_option_value->product_option_value_id,0,$data['quantity']);
        }
        return true;
    }

    private function getOptionValueId($option_id, $option_value_name){
        $option_value_data = OptionValueDescription::where('language_id', $this->config_languge_id)
            ->where('option_id', $option_id)
            ->where('name', $option_value_name)->first();
        if($option_value_data)
            return $option_value_data->option_value_id;

        $data_option_value = [
            'option_id' => $option_id,
            'image' => '',
            'sort_order' => 0,
            'status' => 1,
        ];
        $option_value = OptionValue::create($data_option_value);
        $option_value_id = $option_value->option_value_id;
        $data_option_value_description = [];
        foreach ($this->languages as $language_id => $language_name) {
            $data_option_value_description[] = [
                'option_value_id' => $option_value_id,
                'language_id' => $language_id,
                'option_id' => $option_id,
                'name' => $option_value_name,
                'description' => '',
            ];
        }
        OptionValueDescription::insert($data_option_value_description);

        return  $option_value_id;
    }

    private function getManufacturerOptions($manufacturer_id){
        $type_product = $this->is_shoes ? 'shoes' : 'other';
        if(isset($this->manufacturer_options[$manufacturer_id][$type_product])) {
            return true;
        }

        $options = Option::join('oc_option_description', 'oc_option.option_id', '=', 'oc_option_description.option_id')
            ->where('oc_option_description.language_id', $this->config_languge_id)
            ->where('oc_option.manufacturer_id', $manufacturer_id)
            ->where('oc_option.type_product', $type_product)
            ->select('oc_option.*', 'oc_option_description.name')->get()->toArray();
        if($options){
            foreach($this->main_options as $key => $main_option) {
                foreach($options as $option) {
                    if($main_option['name'] == $option['name']){
                        $this->manufacturer_options[$manufacturer_id][$type_product][$key] = $option['option_id'];
                    }
                }
            }
            if($this->is_shoes){
                $product_option_pro = ProductOptionPro::where('manufacturer_id', $manufacturer_id)->first();
                if($product_option_pro){
                    $this->manufacturer_options[$manufacturer_id]['option_pro_id'] = $product_option_pro->product_option_pro_id;
                }else{
                    $product_option_pro_id = $this->addProductOptionPro($manufacturer_id);
                    $this->manufacturer_options[$manufacturer_id]['option_pro_id'] = $product_option_pro_id;
                }
            }
            return true;
        }else{
            foreach ($this->main_options as $key => $new_option) {
                if($type_product == 'other' && $new_option['name'] != 'Size'){
                    continue;
                }
                $data_option = [
                    'type'=> 'select',
                    'sort_order'=> $key,
                    'manufacturer_id'=> $manufacturer_id,
                    'type_product' => $type_product,
                    "image_width"=> 0,
                    "image_height"=> 0,
                    "image_view"=> 0,
                    "image_view_name" => '',
                ];
                $option = Option::create($data_option);
                $this->manufacturer_options[$manufacturer_id][$type_product][$key] = $option->option_id;
                $data_option_description = [];
                foreach ($this->languages as $language_id => $language_name) {
                    $data_option_description[] = [
                        'option_id' => $option->option_id,
                        'language_id' => $language_id,
                        'name' => $new_option['names'][$language_id],
                    ];
                }
                OptionDescription::insert($data_option_description);
            }
            if($this->is_shoes){
              $product_option_pro_id = $this->addProductOptionPro($manufacturer_id);
                $this->manufacturer_options[$manufacturer_id]['option_pro_id'] = $product_option_pro_id;
            }

            return true;
        }
        return false;
    }
    private function addProductOptionPro($manufacturer_id){
        $data_option_pro = [
            'manufacturer_id' => $manufacturer_id,
            'sort_order' => 0,
            'status' => 1,
            'status_image' => 0,
        ];

       $product_option_pro = ProductOptionPro::create($data_option_pro);
        $data_option_pro_description = [];
        foreach ($this->languages as $language_id => $language_name) {
            $data_option_pro_description[] = [
                'product_option_pro_id' => $product_option_pro->product_option_pro_id,
                'language_id' => $language_id,
                'name' => 'Размер+Цвет+Полнота+Каблук_'.$manufacturer_id
            ];
        }
        ProductOptionProDescription::insert($data_option_pro_description);
        $data_option_pro_kit = [];
        foreach ($this->manufacturer_options[$manufacturer_id]['shoes'] as $key => $option_id) {
            $data_option_pro_kit[] = [
                'product_option_pro_id' => $product_option_pro->product_option_pro_id,
                'sort_order' => $key,
                'option_id' => $option_id,
                'manufacturer_id' => $manufacturer_id
            ];
        }
        ProductOptionProKit::insert($data_option_pro_kit);
        return $product_option_pro->product_option_pro_id;
    }

    public function countingProduct(){
        if($this->update_product_option){
            foreach ($this->update_product_option as $product_id) {
                $sum = ProductOptionValue::selectRaw('SUM(quantity) as quantity')
                    ->where('product_id', $product_id)->first();
                $min_price = ProductOptionValue::selectRaw('MIN(price) as min_price,  base_price, currency_id ')
                    ->where('price', '>', 0)
                    ->where('product_id', $product_id)
                    ->groupBy('product_id')->first();

                $quantity = $sum->quantity ?? 0;
                $price = $min_price->min_price ?? 0;
                $base_price = $min_price->base_price ?? 0;
                $currency_id = $min_price->currency_id ?? 0;
                Product::where('product_id', $product_id)
                    ->update([
                    'quantity' => $quantity,
                    'price' => $price,
                    'currency_id' => $currency_id,
                    'base_price' => $base_price
                ]);
                ProductOptionValue::where('product_id', $product_id)
                    ->where('price', '>', 0)
                    ->update([
                        'price' => DB::raw("price - {$price}"),
                        'base_price' => DB::raw("base_price - {$base_price}"),
                    ]);
                ProductWarehouse::where('product_id',$product_id)->delete();
                ProductWarehouse::create([
                    'product_id' => $product_id,
                    'warehouse_id' => $this->warehouse_id,
                    'quantity' => $quantity
                ]);
            }
        }
        if($this->update_product_option_pro){
            foreach ($this->update_product_option_pro as $product_id) {
                $sum = ProductOptionProValue::selectRaw('SUM(quantity) as quantity')
                    ->where('product_id', $product_id)->first();
                $min_price = ProductOptionProPrice::selectRaw('MIN(price) as min_price,  base_price, currency_id')
                    ->where('price', '>', 0)
                    ->where('customer_group_id', $this->config_customer_group_id)
                    ->where('product_id', $product_id)
                    ->groupBy('product_id')->first();
                $quantity = $sum->quantity ?? 0;
                $price = $min_price->min_price ?? 0;
                $base_price = $min_price->base_price ?? 0;
                $currency_id = $min_price->currency_id ?? 0;
                Product::where('product_id', $product_id)
                    ->update([
                        'quantity' => $quantity,
                        'price' => $price,
                        'currency_id' => $currency_id,
                        'base_price' => $base_price
                    ]);
                ProductWarehouse::where('product_id',$product_id)->delete();
                ProductWarehouse::create([
                    'product_id' => $product_id,
                    'warehouse_id' => $this->warehouse_id,
                    'quantity' => $quantity
                ]);
            }
        }

    }

    private function addProduct($data){
        $product = Product::create([
            'model' => $data['model'],
            'general_code' => $data['code_manufacturer'],
            'sku' => $data['model'],
            'quantity' => $data['quantity'] ?? 0,
            'stock_status_id' => 5,
            'manufacturer_id' => $data['manufacturer_id'],
            'price' => $data['price'] ?? 0,
            'base_price' =>$data['base_price'] ?? 0,
            'currency_id' => $data['currency_id'],
            'tax_class_id' => 9,
            'date_added' => Carbon::now(),
            'status' => 0,
        ]);
        $product_id = $product->product_id;

        $product_description = [];
        foreach ($this->languages as $language_id => $language_name) {
            $product_description[] = [
                'product_id' => $product_id,
                'language_id' => $language_id,
                'name' => $data['model'],
                'color' => $data['color'],
            ];
        }
        ProductDescription::insert($product_description);

       /*Product2Store::create([
            'product_id' => $product_id,
            'store_id' => 0,
        ]);*/

        Log::channel('import_update')->info("Создан новый товар, модель  \"{$data['model']}\" и ID {$product_id}'");
        return $product_id;
    }

    private function addManufacturer($manufacturer_name){
        $manufacturer =  Manufacturer::create([
            'name' => $manufacturer_name
        ]);
        $manufacturer_id = $manufacturer->manufacturer_id;
        $manufacturer_description = [];
        foreach ($this->languages as $language_id => $language_name) {
            $manufacturer_description[] = [
                'manufacturer_id' => $manufacturer_id,
                'language_id' => $language_id,
                'name' => $manufacturer_name,
            ];
        }
        ManufactuterDescription::insert($manufacturer_description);

        Log::channel('import_update')->info("Создан новый производитель с именем {$manufacturer_name} и ID {$manufacturer_id}");
        return  $manufacturer_id;
    }

    private function generateProductUniqueCodes($product_id, $product_option_value_id, $product_option_pro_value_id, $quantity){

        if(!$quantity)
            return false;

        $count = $this->getCountProductUniqueCodes($product_id, $product_option_value_id, $product_option_pro_value_id);

        if($product_option_pro_value_id){
            $postfix = $count+1;
            $generator = new BarcodeGeneratorPNG();
            for($i=0; $i < $quantity; $i++){
                $code=$product_id.$product_option_pro_value_id.'/'.$postfix;
                $this->createProductUniqueCodes($product_id, $product_option_value_id, $product_option_pro_value_id, $code);
                $postfix ++;
            }
            return true;
        }

        if($product_option_value_id){
            $postfix = $count+1;
            for($i=0; $i < $quantity; $i++){
                $code=$product_id.$product_option_value_id.'/'.$postfix;
                $this->createProductUniqueCodes($product_id, $product_option_value_id, $product_option_pro_value_id, $code);
                $postfix ++;
            }
            return true;
        }

        $postfix = $count+1;
        for($i=0; $i < $quantity; $i++){
            $code=$product_id.'/'.$postfix;
            $this->createProductUniqueCodes($product_id, $product_option_value_id, $product_option_pro_value_id, $code);
            $postfix ++;
        }
        return true;

    }
    private function createProductUniqueCodes($product_id, $product_option_value_id, $product_option_pro_value_id, $code){
        $generator = new BarcodeGeneratorPNG();
        $barcode = $generator->getBarcode($code, $generator::TYPE_CODE_128,3,100);
        $image_name = str_replace('/','-',$code);
        $image_barcode = 'catalog/product_barcode/'. $image_name.'.png';
        file_put_contents(env('MAIN_DOMAIN_IMAGE').$image_barcode, $barcode);
        $image_qr = 'catalog/product_qr/'. $image_name.'_qr.svg';
        QrCode::generate(env('MAIN_DOMAIN_PRODUCT_PATH').$product_id.'&qr_code=true',env('MAIN_DOMAIN_IMAGE').$image_qr);
        ProductUniqueCodes::create([
            'product_id' => $product_id,
            'product_option_value_id' => $product_option_value_id,
            'product_option_pro_value_id' => $product_option_pro_value_id,
            'code' => $code,
            'barcode' => $image_barcode,
            'qrcode' => $image_qr
        ]);
    }


        private function getCountProductUniqueCodes($product_id, $product_option_value_id, $product_option_pro_value_id){
        $count = 0;

        if($product_option_pro_value_id){
            $count = ProductUniqueCodes::where('product_id',$product_id)
                ->where('product_option_value_id', 0)
                ->where('product_option_pro_value_id', $product_option_pro_value_id)->count();
            return  $count;
        }

        if($product_option_value_id){
            $count = ProductUniqueCodes::where('product_id',$product_id)
                ->where('product_option_value_id', $product_option_value_id)
                ->where('product_option_pro_value_id',0)->count();
            return  $count;
        }

        $count = ProductUniqueCodes::where('product_id',$product_id)
            ->where('product_option_value_id', 0)
            ->where('product_option_pro_value_id',0)->count();

        return  $count;

    }
}
