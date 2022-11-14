<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product2Store extends Model
{
    use HasFactory;

    protected $table = 'oc_product_to_store';
    protected $primaryKey = '';
    public $timestamps = false;
    protected $fillable = [
        'product_id',
        'store_id',
    ];
}
