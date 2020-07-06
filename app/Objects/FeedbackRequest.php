<?php

namespace App\Objects;

use App\Objects\Archetypes\Question;
use Illuminate\Database\Eloquent\Model;

class FeedbackRequest extends Question
{
    //
    public function options()
    {
        return $this->hasMany('App\Objects\FeedbackOptions', 'question_id');
    }
}
