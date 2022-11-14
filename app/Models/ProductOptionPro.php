<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductOptionPro extends Model
{
    use HasFactory;

    protected $table = 'oc_product_option_pro';
    protected $primaryKey = 'product_option_pro_id';

    protected $fillable = [
        'product_option_pro_id',
        'manufacturer_id',
        'sort_order',
        'status',
        'status_image'
    ];

    public $timestamps = false;
}
