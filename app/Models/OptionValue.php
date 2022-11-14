<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OptionValue extends Model
{
    protected $table = 'oc_option_value';
    protected $primaryKey = 'option_value_id';
    use HasFactory;

    protected $fillable = [
        'option_value_id',
        'option_id',
        'image',
        'sort_order',
        'status'
    ];

    public $timestamps = false;
}
