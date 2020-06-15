<?php

namespace App\Objects;

use Illuminate\Database\Eloquent\Model;

class Operator extends Model
{
    protected $fillable = ['slack_user_id', 'name', 'opt_in'];

    public function sampling_answers()
    {
        return $this->hasMany('App\Model\SamplingAnswers');
    }


    public function prompt_responses()
    {
        return $this->hasMany('App\Model\PromptResponses');
    }

    public function pickSamplingQuestion()
    {
        $answered = $this->sampling_answers;
        $eval = [];
        foreach ($answered as $answer) {

        }
        $question = SamplingQuestion::where('state', 'live')->first();
        return $question;
    }
}
