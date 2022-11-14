<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductOptionProValue extends Model
{
    use HasFactory;
    protected $table = 'oc_product_option_pro_value';
    protected $fillable = [
        'product_id',
        'quantity',
        'sku',
        'model',
    ];

    public $timestamps = false;
}
