<?php

namespace App\Objects;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;

class Operator extends Model
{
    protected $fillable = [ 'slack_user_id', 'name', 'opt_in', 'frequency', 'preferences', 'journey_state' ];

    protected $withCount = [ 'travels' ];

    protected $casts = [ 'preferences' => 'array', 'journey_state' => 'array' ];

    public function prompt_responses()
    {
        return $this->hasMany('App\Objects\PromptResponses');
    }

    public function learnings()
    {
        return $this->hasMany('App\Objects\Learning');
    }

    public function travels()
    {
        return $this->hasMany('App\Objects\Travel');
    }

    public function getKnownTopics()
    {
        $all = $this->learnings;
        $topics = [];
        foreach ($all as $learned) {
            $topics[$learned->knowledge->id] = $learned->knowledge->name;
        }
        return $topics;
    }

    public function getDefaultTopics()
    {
        $topics = Knowledge::where('prerequisites', false)->inRandomOrder()->take(12)->get('name');

        return $topics;
    }

    public function pickSamplingQuestion()
    {
        if (!empty($preferences) && !empty($preferences['topics'])) {
            $topics = $preferences['topics'];
        } else {
            $topics = $this->getKnownTopics();
            if (empty($topics)) {{
                $topics = $this->getDefaultTopics();
            }
        }
        shuffle($topics);

        foreach ($topics as $name) {
            $learned = $this->learnings()->whereHas('knowledge.name', function (Builder $query, $name) {
                $query->where($name);
            })->first();
            if (empty($learned)) {
                $topic = Knowledge::where('name', $name)->first();
                $learned = new Learning();
                $learned->depth = 'vague';
                $learned->level = 'basic';
                $learned->last_evaluation_completed = time();
                $learned->knowledge()->associate($topic);
                $learned->operator()->associate($this);
                $learned->save();
            }
            $level = $learned->evaluageKnowledge();
            $topic = $learned->knowledge;
            $answered = $learned->answers()->get('sampling_question_id');
            $pull = $topic->sampling_questions()->where('level', $level)
                ->whereNotIn('id', $answered)
                ->inRandomOrder()
                ->first();
            if (!empty($pull)) {
                return $pull;
            }
        }
        }
        $pull = SamplingQuestion::where('level', 'basic')
            ->whereNotIn('id', $answered)
            ->inRandomOrder()
            ->first();
        return $pull;
    }
}
