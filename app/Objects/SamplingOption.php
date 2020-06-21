<?php

namespace App\Objects;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use App\Objects\SamplingQuestion;

class SamplingOption extends Model
{
    public $states = [
        'draft', 'review', 'trial', 'live'
    ];
    protected $fillable = [ 'sampling_question_id', 'question_text', 'option', 'correct', 'state' ];
    protected $attributes = [
            'state' => 'draft',
            'question_text' => ''
        ];
    //
    public function sampling_question()
    {
        return $this->belongsTo('SamplingQuestion');
    }

    public function save($options = [])
    {
        //$question = $this->sampling_question();
        //var_dump($question);
        //Log::debug(__METHOD__.': '.$question->question);
        //$this->question_text = $question->question;
        parent::save($options);
    }
}
