<?php

namespace App\Objects\Archetypes;

use App\Objects\FeedbackRecord;
use App\Objects\Operator;
use App\Objects\PromptSegmentResponse;
use App\Objects\SamplingAnswer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Answer extends Model
{
    protected $fillable = [
        'operator_id',
        'question_id',
        'question_text',
        'freeform_answer',
        'selected_options',
        'available_options',
        'eval_percent'
    ];

    protected $casts = [
        'selected_options' => 'array',
        'available_options' => 'array'
    ];

    public function selectedOptionsString()
    {
        $string = "Selected options: ";
        $count = 0;
        foreach ($this->selected_options as $option) {
            $count++;
            $string .= $option;
            if ($count < count($this->selected_options)) {
                $string .= ", ";
            }
        }
        return $string;
    }

    public function evalPercentAsDecimal(){
        return $this->eval_percent / 100;
    }

    public function getBlockId()
    {
        $type = get_class($this);
        $type = str_replace('App\\Objects\\', '', $type);
        $block_id = '';
        switch($type)
        {
            case 'FeedbackRecord':
                $block_id = 'feedback_request.' . $this->question_id . '.' . $this->id;
                break;
            case 'SamplingAnswer':
                Log::debug($this->question);
                $block_id = 'sampling_question.' . $this->question_id . '.' . $this->id;
                break;
            case 'PromptSegmentResponse':
                $block_id = 'prompt_segment.' . $this->question_id . '.' . $this->id;
                break;
            default:
                Log::debug("Unknown class object found in app/Objects/Answer::createBlockId - $type");
                break;
        }
        return $block_id;
    }

    public function getOptionsTableName()
    {
        $type = get_class($this);
        $type = str_replace('App\\Objects\\', '', $type);
        switch($type)
        {
            case 'FeedbackRecord':
                return 'feedback_options';
            case 'SamplingAnswer':
                return 'sampling_options';
            case 'PromptSegmentResponse':
                return 'prompt_segment_options';
            default:
                Log::debug("Unknown class object found in app/Objects/Answer::createBlockId - $type");
                break;
        }
        return 'unknown';
    }

    public function getOptions()
    {
        $selected = $this->available_options;
        $options = [];
        $table = $this->getOptionsTableName();
        foreach ($selected as $id => $info) {
            $options[] = DB::table($table)->find($id);
        }
        return $options;
    }

    public function saveAnswer(Operator $operator)
    {
        $type = get_class($this);
        $type = str_replace('App\\Objects\\', '', $type);
        Log::debug(__METHOD__. ': '.$type);
        if ($type == 'SamplingAnswer') {
            foreach ($this->learnings as $learning) {
                $learning->questions_answered++;
                $learning->save();
            }
            return $operator->needsAQuestion();
        }
        if ($type == 'PromptSegmentResponse') {
            $operator = $this->operator;
            $travel = $operator->getCurrentTravel();
            $travel->completed_segments += 1;
            $travel->save();

            $next = $segment = $operator->getNextSegment();
            if (is_object($next)) {
                return 'segment';
            }
            return $next;
        }
        return true;
    }

    public function answerQuestion($answerType, $answerObject, $answerContent)
    {
        $done = false;
        Log::debug(__METHOD__.": $answerType");
        if ($answerType == 'radio_buttons') {
            $answerContent = [ $answerContent ];
            $travel = $this->operator->getCurrentTravel();
            $travel->completed_segments += 1;
            $travel->save();
            $done = true;
        }
        if (!is_array($answerContent)) {
            $answerObject->freeform_answer = $answerContent;
            $answerObject->save();
            return 'wait';
        }
        $answerObject->selected_options = $answerContent;
        $total = count($answerObject->available_options);
        $score = 0;
        foreach ($answerObject->available_options as $id => $info) {
            if (in_array($info['option'], $answerContent)) {
                $score = $info['correct'] ? $score + 1 : $score - 1;
            } else {
                $score = $info['correct'] ? $score - 1 : $score + 1;
            }
        }
        $answerObject->eval_percent = ($score / $total) * 100;
        $answerObject->save();

        if ($done) return 'segment';
        return 'wait';
    }

}
