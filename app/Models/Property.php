<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    protected $fillable = [
        'title',
        'description',
        'price',
        'area',
        'type',
        'purpose',
        'status',
        'phone',
        'balconies',
        'bedrooms',
        'bathrooms',
        'livingRooms',
        'location_lat',
        'location_lon',
        'user_id',
        'address',
    ] ;

    public function users(){
        return $this->belongsTo(User::class, 'user_id');
    }

    public function images(){
        return $this->morphMany(Image::class, 'imageable');
    }
}
