<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManufactuterDescription extends Model
{
    use HasFactory;

    protected $table = 'oc_manufacturer_description';
    protected $primaryKey = 'manufacturer_id';
    protected $fillable = [
        'manufacturer_id',
        'language_id',
        'name'
    ];
    public $timestamps = false;
}
