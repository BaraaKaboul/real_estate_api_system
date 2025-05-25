<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Premium extends Model
{
    protected $fillable = [
        'name','office_name','office_location','phone','about','plan','duration','status','user_id','start_date','end_date'
    ];
}
