<?php

namespace App\Objects;

use App\Traits\KnowledgableTrait;
use Illuminate\Database\Eloquent\Model;
use App\Traits\EditableTrait;

class SamplingQuestion extends Model
{
    use EditableTrait;
    use KnowledgableTrait;

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
        return $this->belongsToMany('App\Objects\Knowledge', 'knowledges_questions', 'question_id', 'knowledge_id')
            ->using('App\Objects\SamplingQuestionKnowledge');
    }

    public function sampling_options()
    {
        return $this->hasMany('App\Objects\SamplingOption');
    }

    public function editors()
    {
        return $this->belongsToMany('App\Objects\User', 'sampling_questions_users')
            ->using('App\Objects\SamplingQuestionUser')
            ->withPivot([ 'write_access' ]);
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
