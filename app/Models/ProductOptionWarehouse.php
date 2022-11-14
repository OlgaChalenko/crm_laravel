<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductOptionWarehouse extends Model
{
    protected $table = 'oc_product_option_warehouse';
    protected $primaryKey = 'product_warehouse_id';
    protected $fillable = [
        'product_id',
        'product_option_value_id',
        'warehouse_id',
        'quantity'

    ];
    public $timestamps = false;
    use HasFactory;
}
