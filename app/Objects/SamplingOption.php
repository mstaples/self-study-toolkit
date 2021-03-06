<?php

namespace App\Objects;

use App\Objects\Archetypes\Option;

class SamplingOption extends Option
{
    public function question()
    {
        return $this->belongsTo('SamplingQuestion', 'question_id');
    }

}
