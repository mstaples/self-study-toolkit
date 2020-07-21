<?php

namespace App\Objects\Archetypes;

use App\Objects\FeedbackRecord;
use App\Objects\Learning;
use App\Objects\Operator;
use App\Objects\PromptSegmentResponse;
use App\Objects\SamplingAnswer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Question extends Model
{
    public function getKnowledges()
    {
        Log::debug(__METHOD__);
        $type = get_class($this);
        $type = str_replace('App\\Objects\\', '', $type);
        if ($type == 'FeedbackRequest') {
            return [];
        }
        if ($type == 'PromptSegment') {
            Log::debug($type);
            $path = $this->prompt->prompt_path;
            $knowledges = $path->knowledges;
            Log::debug($knowledges);
            return $knowledges;
        }
        return $this->knowledges;
    }

    public function prepareNewQuestionAnswer(Operator $operator)
    {
        $type = get_class($this);
        $type = str_replace('App\\Objects\\', '', $type);
        // Answer records are created when the question is sent out
        switch($type)
        {
            case 'FeedbackRequest':
                $answer = new FeedbackRecord();
                $answer->question_text = $this->request;
                break;
            case 'SamplingQuestion':
                $answer = new SamplingAnswer();
                $answer->question_text = $this->question;
                $operator->answers()->save($answer);
                $operator->save();
                break;
            case 'PromptSegment':
                $prompt = $this->prompt;
                $answer = new PromptSegmentResponse();
                $answer->prompt_id = $prompt->id;
                $answer->question_text = $this->segment_text;

                $travel = $operator->getCurrentTravel();
                Log::debug("Creating a new question and associating it with travel id ".$travel->id);
                $answer->operator()->associate($operator);
                $answer->travel()->associate($travel);
                $answer->save();

                break;
            default:
                Log::debug("Unknown question object given to app/Objects/Question::prepareAnswer - $type");
                break;
        }
        $correctOptions = $this->options()->where('correct', true)->inRandomOrder()->limit(4)->get();
        Log::debug("Correct Options: ");
        Log::debug($correctOptions);
        $incorrectOptions = $this->options()->where('correct', false)->inRandomOrder()->limit(4)->get();
        Log::debug("Incorrect Options: ");
        Log::debug($incorrectOptions);
        $pickCorrect = rand(1,count($correctOptions));
        $options = [];
        foreach ($correctOptions as $option) {
            $options[$option->id] = [ 'correct' => $option->correct, 'option' => $option->option ];
            if (count($options) == $pickCorrect) {
                break;
            }
        }
        foreach ($incorrectOptions as $option) {
            $options[$option->id] = [ 'correct' => $option->correct, 'option' => $option->option ];
            if (count($options) == 5) {
                break;
            }
        }
        Log::debug("Saving These Options: ");
        Log::debug($options);
        $answer->question_id = $this->id;
        $answer->available_options = $options;
        $answer->save();

        if ($type == 'SamplingQuestion' || $type == 'PromptSegment') {
            $knowledges = $this->getKnowledges();
            $travel = $operator->getCurrentTravel();
            foreach ($knowledges as $knowledge) {
                $learning = $operator->learnings()->where('knowledge_id', $knowledge->id)->first();
                if (empty($learning)) {
                    $learning = new Learning();
                    $learning->operator()->associate($operator);
                    $learning->knowledge()->associate($knowledge);
                    $learning->save();
                }
                if ($type == 'PromptSegment') {
                    $learning->travels()->save($travel);
                }
            }
        }

        return $answer;
    }
}
