<?php

namespace app\models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    public $timestamps = false;

    protected $with = [
        'brand'
    ];

    protected $hidden = [
        'brand_id'
    ];

    function brand() {
        return $this->belongsTo('app\models\Brand');
    }
}