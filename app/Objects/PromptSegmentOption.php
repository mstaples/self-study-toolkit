<?php

namespace App\Objects;

use App\Objects\Archetypes\Option;

class PromptSegmentOption extends Option
{
    public function question()
    {
        return $this->belongsTo('PromptSegment', 'question_id');
    }

}
