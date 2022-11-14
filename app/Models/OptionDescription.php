<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OptionDescription extends Model
{
    use HasFactory;
    protected $table = 'oc_option_description';

    protected $fillable = [
        'option_id',
        'language_id',
        'name',
    ];

    public $timestamps = false;
}
