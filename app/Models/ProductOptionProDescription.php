<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductOptionProDescription extends Model
{
    use HasFactory;

    protected $table = 'oc_product_option_pro_description';
    protected $fillable = [
        'product_option_pro_id',
        'language_id',
        'name'
    ];

    public $timestamps = false;
}
