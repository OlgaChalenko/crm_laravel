<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory;

    protected $table = 'oc_country';
    protected $primaryKey = 'country_id';

    public function manufacturer(){
        return $this->hasMany(ManufacturerInfo::class, 'country_id');
    }
}
