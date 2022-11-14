<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManufacturerInfo extends Model
{
    use HasFactory;

    protected $table = 'crm_manufacturer_info';
    protected $primaryKey = 'id';
    protected $fillable = [
            "manufacturer_id",
            "country_id",
            "city",
            "address",
            "telephone",
            "email",
            "bank_details",
            "logo",
    ];
    public $timestamps = false;

    public function manufacturer(){
        return $this->belongsTo(Manufacturer::class, 'manufacturer_id');
    }

    public function country(){
        return $this->belongsTo(Country::class, 'country_id');
    }
}
