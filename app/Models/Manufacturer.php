<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Manufacturer extends Model
{
    use HasFactory;

    protected $table = 'oc_manufacturer';
    protected $primaryKey = 'manufacturer_id';
    protected $fillable = [
        "name",
    ];
    public $timestamps = false;

    public function info(){
        return $this->hasOne(ManufacturerInfo::class, 'manufacturer_id');
    }

    public function products(){
        return $this->hasMany(Product::class, 'manufacturer_id');
    }
}
