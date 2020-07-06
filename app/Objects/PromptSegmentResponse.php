<?php

namespace App\Objects;

use App\Objects\Archetypes\Answer;

class PromptSegmentResponse extends Answer
{
    //
    protected $fillable = [
        'travel_id',
        'operator_id',
        'question_id',
        'question_text',
        'freeform_answer',
        'selected_options',
        'eval_percent',
    ];

    public function travel()
    {
        return $this->belongsTo('App\Objects\Travel', 'travel_id');
    }

    public function operator()
    {
        return $this->belongsTo('App\Objects\Operator');
    }
}
