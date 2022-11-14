<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Option extends Model
{
    use HasFactory;
    protected $table = 'oc_option';
    protected $primaryKey = 'option_id';

    protected $fillable = [
        'option_id',
        'type',
        'sort_order',
        'manufacturer_id',
        'type_product',
        "image_width",
        "image_height",
        "image_view",
        "image_view_name",
    ];

    public $timestamps = false;
}
