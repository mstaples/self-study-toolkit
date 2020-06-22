<?php

namespace App\Objects;

use Illuminate\Database\Eloquent\Model;

class Knowledge extends Model
{
    protected $fillable = [ 'name', 'description', 'prerequisites' ];

    protected $attributes = [
        'prerequisites' => false,
        'description' => ''
    ];

    protected $table = 'knowledges';

    public function prerequisites()
    {
        return $this->belongsToMany('App\Objects\Knowledge')->as('knowledges_prerequisites');
    }

    public function paths()
    {
        return $this->belongsToMany('App\Objects\Paths');
    }

    public function questions()
    {
        return $this->belongsToMany('App\Objects\SamplingQuestion', 'knowledges_questions', 'knowledge_id', 'question_id')
            ->using('SamplingQuestionKnowledge');
    }

    public function feedback_requests()
    {
        return $this->hasMany('App\Objects\FeedbackRequest');
    }
}
