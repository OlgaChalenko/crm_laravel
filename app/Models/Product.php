<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $table = 'oc_product';
    protected $primaryKey = 'product_id';
    public $timestamps = false;
    protected $fillable = [
        'model',
        'general_code',
        'sku',
        'quantity',
        'stock_status_id',
        'manufacturer_id',
        'price',
        'base_price',
        'currency_id',
        'status',
        'date_added',
        'tax_class_id',
    ];


    public function manufacturer(){
        return $this->belongsTo(Manufacturer::class, 'manufacturer_id');
    }
}
