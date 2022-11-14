<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductWarehouse extends Model
{
    protected $table = 'oc_product_warehouse';
    protected $primaryKey = 'product_warehouse_id';
    protected $fillable = [
        'product_id',
        'warehouse_id',
        'quantity'

    ];
    public $timestamps = false;
    use HasFactory;
}
