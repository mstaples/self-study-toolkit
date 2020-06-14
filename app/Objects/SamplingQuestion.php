<?php

namespace App\Objects;

use Illuminate\Database\Eloquent\Model;

class SamplingQuestion extends Model
{
    protected $stateOptions = [ 'review', 'trial', 'live' ];
    protected $difficultyOptions = [ 'vague', 'passing', 'familiar', 'deep' ];

    protected $fillable = [ 'state', 'question_difficulty', 'question', 'answer_options' ];
    protected $casts = [ 'answer_options' => 'array' ];
    protected $attributes = [ 'state' => 'review' ];

    public function getBlockId()
    {
        return 'SQ' . $this->id;
    }

    public function prompt_path()
    {
        return $this->belongsTo('App\Objects\PromptPath');
    }

    public function sampling_options()
    {
        return $this->hasMany('App\Objects\SamplingOption');
    }

    public function getDifficulties()
    {
        return $this->difficultyOptions;
    }

    public function getStates()
    {
        return $this->stateOptions;
    }
}
