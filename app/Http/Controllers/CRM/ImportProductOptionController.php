<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Imports\ImportProductOption;
use App\Models\Currency;
use App\Models\CustomerGroup;
use App\Models\Language;
use App\Models\Manufacturer;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ImportProductOptionController extends Controller
{
    public function importProductOptionForm(){
        return view('crm.import.import_product_option_form');
    }

    public function importProductOption(Request $request){
            $request->validate([
                'file' => ['required', 'mimes:xlsx'],
            ]);
            $importProduct = new ImportProductOption();

            $manufacturers_all = Manufacturer::all();
            $manufacturers = [];
            foreach ($manufacturers_all as $manufacturer) {
                $manufacturers[$manufacturer['name']] = $manufacturer['manufacturer_id'];
            }

            $currencies_all = Currency::all();
            $currencies = [];
            foreach ($currencies_all as $currency) {
                $currencies[$currency['code']] = $currency['currency_id'];
            }

            $languages_all = Language::all();
            $languages = [];
            foreach ($languages_all as $language) {
                $languages[$language['language_id']] = $language['name'];
            }

            $customer_group_all = CustomerGroup::all();
            $customer_groups = [];
            foreach ($customer_group_all as $customer_group) {
                $customer_groups[] = $customer_group['customer_group_id'];
            }

            $importProduct->manufacturers = $manufacturers;
            $importProduct->currencies = $currencies;
            $importProduct->languages = $languages;
            $importProduct->customer_groups = $customer_groups;

            Excel::import($importProduct, $request['file']);
            $importProduct->countingProduct();

        return redirect()->route('import.import_product_option_form')->with('success', 'Информация по поставщику обновлена!');
    }
}
