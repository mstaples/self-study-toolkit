<?php

namespace App\Objects;

use App\Objects\Archetypes\Option;

class FeedbackOption extends Option
{
    public function question()
    {
        return $this->belongsTo('FeedbackRequest', 'question_id');
    }
}
