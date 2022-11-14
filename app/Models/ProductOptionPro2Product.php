<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductOptionPro2Product extends Model
{
    use HasFactory;

    protected $table = 'oc_product_option_pro_to_product';
    protected $fillable = [
        'product_id',
        'product_option_pro_id',
    ];

    public $timestamps = false;
}
