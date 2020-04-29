<?php

namespace App\Objects;

use Illuminate\Database\Eloquent\Model;

class SamplingQuestion extends Model
{
    protected $fillable = [ 'state', 'question_difficulty', 'question', 'answer_options' ];
    protected $casts = [ 'answer_options' => 'array' ];
    protected $attributes = [ 'state' => 'review' ];

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
        return [
            'vague', 'passing', 'familiar', 'deep'
        ];
    }
}
