<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductOptionProWarehouse extends Model
{
    protected $table = 'oc_product_related_option_warehouse';
    protected $primaryKey = 'product_warehouse_id';
    protected $fillable = [
        'product_id',
        'related_option_id',
        'warehouse_id',
        'quantity'
    ];
    public $timestamps = false;
    use HasFactory;
}
