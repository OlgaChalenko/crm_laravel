<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductOptionValue extends Model
{
    protected $table = 'oc_product_option_value';
    protected $primaryKey = 'product_option_value_id';
    protected $fillable = [
        'product_option_value_id',
        'product_option_id',
        'product_id',
        'option_id',
        'option_value_id',
        'quantity',
        'subtract',
        'base_price',
        'price',
        'currency_id',
        'price_prefix',
        'points',
        'points_prefix',
        'weight',
        'weight_prefix',
        'upc'
    ];

    public $timestamps = false;
    use HasFactory;
}
