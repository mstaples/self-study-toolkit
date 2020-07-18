<?php

namespace App\Objects;

use App\Objects\Archetypes\Answer;
use Illuminate\Support\Facades\Log;

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

    public function getSegmentOptions()
    {
        $options = $this->getOptions();
        $segment = PromptSegment::find($this->question_id);
        $operator = $this->operator;
        $last = $operator->getLastAnswer($segment);
        if ($last) $last = $last->selected_options;
        $return = [];
        foreach ($options as $id => $option) {
            $return[$id]['option'] = $option->option;
            if ($last && in_array($id, $last)) {
                $return[$id]['has'] = true;
            }
        }
        return $return;
    }
}
