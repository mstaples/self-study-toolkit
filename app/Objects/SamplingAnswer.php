<?php

namespace App\Objects;

use Illuminate\Database\Eloquent\Model;

class SamplingAnswer extends Model
{
    protected $fillable = [ 'sampling_question_id', 'sampling_question', 'answer', 'correct' ];
    //
    public function operator()
    {
        return $this->belongsTo('App/Objects/Operator');
    }
}
