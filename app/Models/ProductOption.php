<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductOption extends Model
{
    protected $table = 'oc_product_option';
    protected $primaryKey = 'product_option_id';

    protected $fillable = [
        'product_id',
        'option_id',
        'value',
        'required',
    ];

    public $timestamps = false;

    use HasFactory;
}
