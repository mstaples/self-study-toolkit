<?php

namespace App\Objects;

use App\Objects\Archetypes\Answer;
use App\Objects\Learnings;

class SamplingAnswer extends Answer
{
    protected $fillable = [
        'question_id',
        'operator_id',
        'question_text',
        'freeform_answer',
        'selected_options',
        'eval_percent',
        'depth'
    ];
    //
    public function operator()
    {
        return $this->belongsTo('App\Objects\Operator', 'operator_id');
    }

    public function learnings()
    {
        return $this->belongsToMany('App\Objects\Learning', 'learnings_answers', 'answer_id');
    }
}
