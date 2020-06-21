<?php

namespace App\Objects;

use Illuminate\Database\Eloquent\Model;

class SamplingAnswer extends Model
{
    // depths 'vague', 'passing', 'familiar', 'deep'
    protected $fillable = [ 'sampling_question_id', 'sampling_question', 'answer', 'correct', 'depth', ];
    //
    public function operator()
    {
        return $this->belongsTo('App/Objects/Operator');
    }

    public function sampling_question()
    {
        return $this->belongsTo('App/Objects/SamplingQuestion');
    }
}
