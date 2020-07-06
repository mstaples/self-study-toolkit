<?php

namespace App\Objects;

use Illuminate\Database\Eloquent\Model;

class Preference extends Model
{

    protected $fillable = [ 'type', 'name', 'operator_id' ];

    public function operator()
    {
        return $this->belongsTo('App\Objects\Operator');
    }
}
