<?php

namespace App\Objects;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

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
            ->using('App\Objects\SamplingQuestionKnowledge');
    }

    public function feedback_requests()
    {
        return $this->hasMany('App\Objects\FeedbackRequest');
    }

    public function save($options = [])
    {
        if ((array_key_exists('name', $options) && strlen($options['name'] < 3) ||
            strlen($this->name) < 3) ) {
            Log::debug(__METHOD__.": given unexpectedly short name value: ".$options['name']);
            return false;
        }
        parent::save($options);
    }
}
