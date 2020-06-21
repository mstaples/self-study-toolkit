<?php

namespace App\Objects;

use Illuminate\Database\Eloquent\Model;

class SamplingQuestion extends Model
{
    protected $stateOptions = [ 'review', 'trial', 'live' ];
    protected $depthOptions = [ 'vague', 'passing', 'familiar', 'deep' ];

    protected $fillable = [ 'state', 'depth', 'question' ];
    protected $attributes = [ 'state' => 'review' ];

    public function getBlockId()
    {
        return 'SQ' . $this->id;
    }

    public function knowledges()
    {
        return $this->belongsToMany('App\Objects\Knowledge', 'knowledges_questions', 'path_id', 'question_id');
    }

    public function sampling_options()
    {
        return $this->hasMany('App\Objects\SamplingOption');
    }

    public function getDepths()
    {
        return $this->depthOptions;
    }

    public function getStates()
    {
        return $this->stateOptions;
    }
}
