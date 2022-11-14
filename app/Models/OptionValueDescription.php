<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OptionValueDescription extends Model
{
    protected $table = 'oc_option_value_description';

    use HasFactory;

    protected $fillable = [
        'option_value_id',
        'language_id',
        'option_id',
        'name',
        'description',
    ];

    public $timestamps = false;
}
