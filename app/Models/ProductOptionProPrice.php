<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductOptionProPrice extends Model
{
    use HasFactory;
    protected $table = 'oc_product_option_pro_price';
    protected $fillable = [
        'product_option_pro_value_id',
        'product_id',
        'customer_group_id',
        'price',
        'base_price',
        'currency_id',
    ];

    public $timestamps = false;
}
