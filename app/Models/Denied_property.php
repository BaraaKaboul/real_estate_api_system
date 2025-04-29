<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Denied_property extends Model
{
    protected $fillable = [
        'property_id',
        'user_id'
    ];
}
