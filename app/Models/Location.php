<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;
    protected $fillable = [
        'Address',
        'Latitude',
        'Longitude',
        'Country_long',
        'Country_short',
        'City_long',
        'City_short',
        'Street_long',
        'Street_short',
        'StreetNum_long',
        'StreetNum_Short',
        'placeId',
        'itemId'

    ];

    public function toString(){
        return $this->Latitude .' '. $this->Longitude.' '.$this->Address;
    }
}
