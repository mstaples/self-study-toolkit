<?php

namespace App\Objects;

use App\Objects\Archetypes\Answer;

class FeedbackRecord extends Answer
{
    protected $fillable = [
        'operator_id',
        'author_id',
        'feedback_request_id',
        'question_text',
        'freeform_answer',
        'selected_options',
        'eval_percent',
        'relationship'
    ];

    public function operator()
    {
        return $this->belongsTo('Operator');
    }
}
