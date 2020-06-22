<?php

namespace App\Objects;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Operator extends Model
{
    public $preferences = [];

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

    public function answers()
    {
        return $this->hasMany('App\Objects\SamplingAnswer');
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

    public function pickTopic()
    {
        $preferences = $this->preferences;
        $path = new PromptPath();
        if (!empty($preferences) && !empty($preferences['topics'])) {
            $topics = $preferences['topics'];
        } else {
            $topics = $this->getKnownTopics();
            if (empty($topics)) {
                $topics = $path->getDefaultTopics();
            }
        }
        shuffle($topics);
        return array_pop($topics);
    }

    public function learn($name)
    {
        $topic = Knowledge::where('name', $name)->first();

        $learned = new Learning();
        $learned->depth = 'vague';
        $learned->level = 'basic';
        $learned->last_evaluation_completed = time();
        $learned->knowledge()->associate($topic);
        $learned->operator()->associate($this);
        $learned->save();

        return $learned;
    }

    public function getTopicPreferences()
    {
        Log::debug(__METHOD__);
        $path = new PromptPath();
        $all = $path->allKnowledges();
        $set = [];
        if (!array_key_exists('topics', $this->preferences)) {
            $this->preferences['topics'] = [];
        }
        Log::debug($this->preferences);
        foreach ($all as $topic) {
            if (!in_array($topic, $this->preferences['topics'])) {
                $set[$topic] = false;
                continue;
            }
            $set[$topic] = true;
        }
        return $set;
    }

    public function retrieveMemory($name)
    {
        $memory = $this->learnings()->whereHas('knowledge', function (Builder $query) use ($name) {
            $query->where('name', $name);
        })->first();
        if (empty($memory)) {
            $memory =  $this->learn($name);
        }
        return $memory;
    }

    public function pickSamplingQuestion()
    {
        Log::debug(__METHOD__);
        $name = $this->pickTopic();
        $concept = $this->retrieveMemory($name);
        $answered = $concept->answers()->get('sampling_question_id');
        Log::debug($answered);
        $pull = $concept->knowledge
            ->sampling_questions()
            ->where('depth', $concept->depth)
            ->whereNotIn('id', $answered)
            ->inRandomOrder()
            ->first();
        if (!empty($pull)) {
            return $pull;
        }
        $pull = SamplingQuestion::where('depth', 'vague')
            ->whereNotIn('id', $answered)
            ->inRandomOrder()
            ->first();
        return $pull;
    }

    public function needsAQuestion()
    {
        Log::debug(__METHOD__);
        $lastPath = $this->travels()->where('completed', true)->orderByDesc('completed_on')->first();
        if (empty($lastPath)) { // new operator
            $lastQuestions = $this->answers()->orderByDesc('created_at')->get();
            $count = 5;
        } else {
            $lastQuestions = $this->answers()
                ->where('completed_on', '>=', $lastPath->completed_on)
                ->get();
            $count = 3;
        }
        $questionCount = count($lastQuestions);
        if ($questionCount >= $count) {
            return false;
        }
        return true;
    }

    public function pathOptions()
    {
        $topicPreferences = $this->preferences['topics'];
        $paths = [];
        foreach ($topicPreferences as $topic) {
            $concept = $this->retrieveMemory($topic);
            $level = $concept->level;
            $matchingPaths = PromptPath::where(['state' => 'live', 'path_level' => $level])
               ->get();
            foreach ($matchingPaths as $path) {
                if (!array_key_exists($path->id, $paths)) {
                    $paths[$path->id] = $path->path_title;
                }
            }
        }
        Log::debug($paths);
        return $paths;
    }
}
