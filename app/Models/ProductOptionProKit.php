<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductOptionProKit extends Model
{
    use HasFactory;
    protected $table = 'oc_product_option_pro_kit';
    protected $fillable = [
        'product_option_pro_id',
        'sort_order',
        'option_id',
        'manufacturer_id'
    ];

    public $timestamps = false;
}
