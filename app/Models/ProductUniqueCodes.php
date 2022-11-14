<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductUniqueCodes extends Model
{
    protected $table = 'oc_product_unique_codes';
    protected $primaryKey = 'id';
    protected $fillable = [
        'place',
        'product_id',
        'product_option_value_id',
        'product_option_pro_value_id',
        'code',
        'barcode',
        'qrcode'
    ];
    public $timestamps = false;
    use HasFactory;
}
