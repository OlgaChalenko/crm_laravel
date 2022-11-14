<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductOptionProDetail extends Model
{
    use HasFactory;
    protected $table = 'oc_product_option_pro_detail';
    protected $fillable = [
        'product_option_pro_value_id',
        'product_id',
        'option_id',
        'option_value_id'
    ];

    public $timestamps = false;
}
