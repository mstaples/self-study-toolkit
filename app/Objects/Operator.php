<?php

namespace App\Objects;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class Operator extends Model
{
    public $frequencies = [
            "constant" => [ 'monthly_prompt_target' => 30, 'optimal_days_between_prompts' => 0 ],
            "3 per week" => [ 'monthly_prompt_target' => 12, 'optimal_days_between_prompts' => 2 ],
            "2 per week" => [ 'monthly_prompt_target' => 8, 'optimal_days_between_prompts' => 3 ],
            "1 per week" => [ 'monthly_prompt_target' => 4, 'optimal_days_between_prompts' => 7 ],
            "2 per month" => [ 'monthly_prompt_target' => 2, 'optimal_days_between_prompts' => 14 ],
            "1 per month" => [ 'monthly_prompt_target' => 1, 'optimal_days_between_prompts' => 30 ],
        ];

    protected $fillable = [ 'slack_user_id', 'name', 'opt_in', 'frequency', 'user_id', 'code' ];

    protected $withCount = [ 'travels' ];

    public function user()
    {
        return $this->belongsTo('App\Objects\User');
    }

    public function learnings()
    {
        return $this->hasMany('App\Objects\Learning');
    }

    public function preferences()
    {
        return $this->hasMany('App\Objects\Preference');
    }

    public function travels()
    {
        return $this->hasMany('App\Objects\Travel');
    }

    public function answers()
    {
        return $this->hasMany('App\Objects\SamplingAnswer');
    }

    public function authoredFeedback()
    {
        return $this->hasMany('App\Objects\FeedbackRecord', 'author_id');
    }

    public function getLastAnswer(PromptSegment $segment)
    {
        return PromptSegmentResponse::where([
            'operator_id' => $this->id,
            'question_id' => $segment->id
        ])
            ->whereNotNull('selected_options')
            ->orderByDesc('updated_at')
            ->first();
    }

    public function startPromptDemo($prompt_id)
    {
        if (!$this->user) {
            return false;
        }
        Prompt::findOrFail($prompt_id);
        $json = [
            'type' => 'demo prompt',
            'prompt_id' => $prompt_id,
            'user_id' => $this->user->id,
            'slack_user_id' => $this->slack_user_id
        ];
        return Artisan::call('update:slack-home', [
            'json' => $json
        ]);
    }

    public function startPathDemo($path_id)
    {
        if (!$this->user) {
            return false;
        }
        PromptPath::findOrFail($path_id);
        $json = [
            'type' => 'demo path',
            'path_id' => $path_id,
            'user_id' => $this->user->id,
            'slack_user_id' => $this->slack_user_id
        ];
        return Artisan::call('update:slack-home', [
            'json' => $json
        ]);
    }

    public function sendSlackConnectUserForm($user_id)
    {
        $json = [
            'type' => 'connect_editor_account',
            'connect_code' => $this->code,
            'user_id' => $user_id,
            'slack_user_id' => $this->slack_user_id
        ];
        return Artisan::call('update:slack-home', [
            'json' => $json
        ]);
    }

    public function connectUser($user_id, $code)
    {
        $user = User::find($user_id);
        if ($user_id == $user->id && $this->code == $code) {
            $user->operator()->save($this);
            $user->save();
            return true;
        }
        return false;
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

    public function getPreferredTopics()
    {
        $all = $this->preferences()->where('type', 'topics')->get();
        $topics = [];
        foreach ($all as $preference) {
            $knowledge = Knowledge::where('name', $preference->name)->first();
            $topics[$knowledge->id] = $knowledge->name;
        }
        return $topics;
    }

    public function pickTopic()
    {
        $topics = $this->getPreferredTopics();
        $path = new PromptPath();
        if (empty($topics)) {
            $topics = $this->getKnownTopics();
            if (empty($topics)) {
                $topics = $path->getDefaultTopics();
            }
        }
        shuffle($topics);
        return array_pop($topics);
    }

    public function retrieveAnswer($question_type, $answerId)
    {
        $known_types = [ 'sampling_question', 'feedback_request', 'prompt_segment' ];
        if (!in_array($question_type, $known_types)) {
            Log::debug("Unknown question type supplied to Operator::findOrRetrieveAnswer - $question_type");
            return false;
        }
        // Answer records are created when the question is sent out
        switch($question_type)
        {
            case 'feedback_request':
                $answer = FeedbackRecord::find($answerId);
                break;
            case 'sampling_question':
                $answer = SamplingAnswer::find($answerId);
                break;
            case 'prompt_segment':
                $answer = PromptSegmentResponse::find($answerId);
                break;
        }

        return $answer;
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
        $all = Knowledge::all();
        $set = [];
        foreach ($all as $topic) {
            $known = $this->preferences()->where([
                [ 'type', '=', 'topics' ],
                [ 'name', '=', $topic->name ]
            ])->first();
            if (!empty($known)) {
                $set[$topic->name] = [ 'option' => $topic->name, 'has' => true ];
                continue;
            }
            $set[$topic->name] = [ 'option' => $topic->name, 'has' => false ];
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
        Log::debug($name);
        Log::debug(class_basename($concept));
        $answers = $this->answers()
            ->select('question_id')
            ->whereNotNull('selected_options')
            ->get();
        $answered = [];
        foreach ($answers as $answer) {
            $answered[] = $answer->question_id;
        }
        Log::debug($answered);
        $pull = $concept->knowledge
            ->questions()
            ->where('depth', $concept->depth)
            ->whereNotIn('question_id', $answered)
            ->inRandomOrder()
            ->first();
        if (!empty($pull)) {
            return $pull;
        }
        $pull = SamplingQuestion::where('depth', 'vague')
            ->whereNotIn('id', $answered)
            ->inRandomOrder()
            ->first();
        if (!empty($pull)) {
            return $pull;
        }
        $pull = SamplingQuestion::where('depth', 'passing')
            ->whereNotIn('id', $answered)
            ->inRandomOrder()
            ->first();
        if (!empty($pull)) {
            return $pull;
        }
        $pull = SamplingQuestion::where('depth', 'familiar')
            ->whereNotIn('id', $answered)
            ->inRandomOrder()
            ->first();
        if (!empty($pull)) {
            return $pull;
        }
        $pull = SamplingQuestion::where('depth', 'deep')
            ->whereNotIn('id', $answered)
            ->inRandomOrder()
            ->first();
        if (!empty($pull)) {
            return $pull;
        }
        $pull = SamplingQuestion::inRandomOrder()
            ->limit(5)
            ->get();
        $avoidBoredom = [];
        foreach ($pull as $question) {
            $answer = $this->answers()
                ->where('question_id', $question->id)
                ->orderBy('updated_at', 'desc')
                ->first();
            Log::debug($answer);
            $avoidBoredom[$question->id] = $answer['updated_at'];
        }
        uasort($avoidBoredom, function($a, $b) {
            return $a->gt($b);
        });
        Log::debug(__LINE__);
        Log::debug($avoidBoredom);
        $question_id = array_key_first($avoidBoredom);
        Log::debug($question_id);
        return SamplingQuestion::find($question_id);
    }

    public function needsAQuestion()
    {
        Log::debug(__METHOD__);
        if ($this->isResting()) {
            return 'rest';
        }
        $currentTravel = $this->getCurrentTravel();
        if (!empty($currentTravel) && (
                $currentTravel->completed_prompts > 0 ||
                $currentTravel->completed_segments > 0
            )) {
            return 'segment';
        }
        $lastPath = $this->travels()->where('completed', true)->orderByDesc('completed_at')->first();
        if (empty($lastPath)) { // new operator
            $lastQuestions = $this->answers()->orderByDesc('created_at')->get();
            $count = 5;
        } else {
            $lastQuestions = $this->answers()
                ->where('created_at', '>=', $lastPath->completed_at)
                ->get();
            $count = 3;
        }
        $questionCount = count($lastQuestions);
        if ($questionCount >= $count) {
            return 'prompt';
        }
        return 'question';
    }

    public function pathOptions()
    {
        $topicPreferences = $this->preferences()->where('type', 'topics')->get();
        $paths = [];
        foreach ($topicPreferences as $topic) {
            $concept = $this->retrieveMemory($topic->name);
            $level = $concept->level;
            $matchingPaths = PromptPath::where(['state' => 'live', 'path_level' => $level])
               ->get();
            foreach ($matchingPaths as $path) {
                if (!array_key_exists($path->id, $paths)) {
                    $paths[$path->id] = [
                        'name' => $path->path_title,
                        'description' => $path->path_thesis
                    ];
                }
            }
        }
        Log::debug($paths);
        return $paths;
    }

    public function getCurrentTravel()
    {
        $current = $this->travels()->where('completed', false)->orderBy('created_at', 'desc')->first();
        if (empty($current)) {
            Log::debug(__METHOD__.": No current travel found");
            return [];
        }
        return $current;
    }

    public function getNextSegment()
    {
        $travel = $this->getCurrentTravel();
        if (empty($travel) || $travel->completed) return $this->needsAQuestion();
        $prompt = $travel->current_prompt;
        if (!empty($prompt)) {
            $completed = $travel->completed_segments;
            if ($completed == 0) {
                return $prompt->prompt_segments()->where('prompt_segment_order', 1)->first();
            }
            $next = $completed + 1;
            $segment = $prompt->prompt_segments()->where('prompt_segment_order', $next)->first();
            if (!empty($segment)) {
                return $segment;
            }
            $travel->completed_prompts += 1;
            $travel->completed_segments = 0;
            $travel->save();
            $nextPrompt = $this->getNextPrompt();
            Log::debug(__METHOD__);
            Log::debug($nextPrompt);
            if ($nextPrompt == "rest") return $nextPrompt;
            if (empty($nextPrompt)) {
                $travel->completed = true;
                $travel->completed_at = now();
                $travel->save();

                return $this->needsAQuestion();
            }
            $travel->current_prompt()->dissociate();
            $travel->current_prompt()->associate($nextPrompt);
            $travel->save();

            $segment = $nextPrompt->prompt_segments()->where('prompt_segment_order', 1)->first();
            return $segment;
        }

        $last = $travel->responses()->orderBy('updated_at', 'desc')->first();
        if ($travel->completed_prompts == 0 || empty($last)) {
            $prompt = $travel->prompt_path->prompts()->where('prompt_path_step', 1)->first();
            $travel->current_prompt()->associate($prompt);
            $travel->save();
            $segment = $prompt->prompt_segments()->where('prompt_segment_order', 1)->first();
            return $segment;
        }
        $travel->completed_prompts += 1;
        $travel->completed_segments = 0;
        $travel->save();

        $prompt = $this->getNextPrompt();
        if ($prompt == "rest") return "rest";
        Log::debug($prompt);
        if (empty($prompt)) return [];
        $travel->current_prompt()->associate($prompt);
        $travel->save();

        return "rest";
    }

    public function isResting()
    {
        Log::debug(__METHOD__);
        $prefer = $this->preferences()->where('type', 'frequency')->first();
        if (!empty($prefer) && $prefer->name == 'constant') {
            return false;
        }
        $lastTravel = $this->travels()
            ->where('completed', true)
            ->orderByDesc('completed_at')
            ->first();
        if (empty($lastTravel)) {
            Log::debug(__METHOD__.": ".__LINE__);
            return false;
        }
        $lastQuestion = $this->answers()
            ->where('selected_options', '!=', null)
            ->orderByDesc('updated_at')
            ->first();
        if ($lastTravel->completed_at->gt($lastQuestion->updated_at)) {
            Log::debug(__METHOD__.": ".__LINE__);
            return false;
        }
        Log::debug(__METHOD__.": ".__LINE__);
        return !$lastTravel->readyForNextPrompt();
    }

    public function getNextPrompt()
    {
        Log::debug(__METHOD__);
        if ($this->isResting()) {
            return "rest";
        }
        $travel = $this->getCurrentTravel();
        if (empty($travel)) {
            return 'path';
        }
        $path = $travel->prompt_path;
        $optimalPromptCount = $this->getOptimalPromptCount($path);
        Log::debug("goal # of prompts: $optimalPromptCount; current #: ".$travel->completed_prompts);
        $remainingPrompts = $optimalPromptCount - $travel->completed_prompts;
        if ($remainingPrompts < 1) {
            $travel->completed = true;
            $travel->save();

            return [];
        }
        $nextRequired = $path->ordered_prompts()
            ->where([
                [ 'optional', '=', false ],
                [ 'prompt_path_step', '>', $travel->completed_prompts ]
            ])
            ->first();
        $nextOptional = $path->ordered_prompts()
            ->where([
                [ 'optional', '=', true ],
                [ 'prompt_path_step', '>', $travel->completed_prompts ]
            ])
            ->first();
        Log::debug($nextOptional);
        Log::debug($nextRequired);

        if (empty($nextOptional) && empty($nextRequired)) {
            return $path->prompts()
                ->where([
                    [ 'repeatable', '=', true ],
                    [ 'prompt_path_step', '<', $travel->completed_prompts + 1 ]
                ])
                ->inRandomOrder()
                ->first();
        }
        if (empty($nextOptional)) return $nextRequired;
        if (empty($nextRequired)) return $nextOptional;
        if ($nextOptional->prompt_path_step > $nextRequired->prompt_path_step ||
            $nextRequired->prompt_path_step >= ($travel->completed_prompts + 1)) {
            return $nextRequired;
        }
        if (($nextRequired->prompt_path_step - $nextOptional->prompt_path_step) == 1) {
            return $nextOptional;
        }
        return $path->prompts()
            ->where([
                [ 'repeatable', '=', true ],
                [ 'prompt_path_step', '>', $travel->completed_prompts ],
                [ 'prompt_path_step', '<', $nextRequired->prompt_path_step ]
            ])
            ->inRandomOrder()
            ->first();
    }

    public function getOptimalPromptCount(PromptPath $path)
    {
        $required = $path->prompts()->where( 'optional', false )->count();
        $optional = $path->prompts()->where( 'optional', true )->count();
        $repeatable = $path->prompts()->where('repeatable', true)->count();

        $preferences = $this->preferences()->where('type', 'frequency')->first();
        if (empty($preferences)) {
            $prefer = array_key_first($this->frequencies);
        } else {
            $prefer = $preferences->name;
        }
        $target = $this->frequencies[$prefer]['monthly_prompt_target'];
        if ($target == '1 per month') return $required;
        $max = $required + $optional + $repeatable;
        if ($max < $target) return $max;
        if ($required > $target) return $required;
        if ($required + $optional >= $target) return $target;
        if ($repeatable > 0) return $target;
        return $required + $optional;
    }
}
