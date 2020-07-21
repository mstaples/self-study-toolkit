<?php

namespace App\Objects;

use App\Traits\KnowledgableTrait;
use App\Traits\PathTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;

class Travel extends Model
{
    use PathTrait;
    use KnowledgableTrait;

    protected $table = 'travels';

    protected $fillable = [ 'completed_segments', 'completed_prompts', 'operator_id', 'prompt_path_id', 'completed', 'completed_at', 'level', 'notebook', 'current_prompt' ];
    //
    protected $attributes = [
        'completed' => false,
        'completed_segments' => 0,
        'completed_prompts' => 0
    ];

    protected $dates = ['completed_at'];

    public function prompt_path()
    {
        return $this->belongsTo('App\Objects\PromptPath');
    }

    public function operator()
    {
        return $this->belongsTo('App\Objects\Operator');
    }

    public function current_prompt()
    {
        return $this->belongsTo('App\Objects\Prompt', 'prompt_id');
    }

    public function responses()
    {
        return $this->hasMany('App\Objects\PromptSegmentResponse');
    }

    public function learnings()
    {
        return $this->belongsToMany('App\Objects\Learnings');
    }

    public function getLastSegment()
    {
        $last = $this->responses()
            ->whereNotNull('selected_options')
            ->orderByDesc('created_at')
            ->first();
        if (empty($last)) return [];
        $segment_id = $last->question_id;
        return PromptSegment::find($segment_id);
    }

    public function getNextPrompt()
    {
        Log::debug(__METHOD__);
        $path = $this->prompt_path;
        $operator = $this->operator;
        $optimalPromptCount = $operator->getOptimalPromptCount($path);
        Log::debug("goal # of prompts: $optimalPromptCount; current #: ".$this->completed_prompts);
        $remainingPrompts = $optimalPromptCount - $this->completed_prompts;
        if ($remainingPrompts < 1) {
            $this->completed = true;
            $this->completed_at = now();
            $this->save();

            return [];
        }
        $nextRequired = $path->ordered_prompts()
            ->where([
                [ 'optional', '=', false ],
                [ 'prompt_path_step', '>', $this->completed_prompts ]
            ])
            ->first();
        $nextOptional = $path->ordered_prompts()
            ->where([
                [ 'optional', '=', true ],
                [ 'prompt_path_step', '>', $this->completed_prompts ]
            ])
            ->first();
        Log::debug($nextOptional);
        Log::debug($nextRequired);

        if (empty($nextOptional) && empty($nextRequired)) {
            $this->completed = true;
            $this->completed_at = now();
            $this->save();

            return [];
        }
        if (empty($nextOptional)) return $nextRequired;
        if (empty($nextRequired)) return $nextOptional;
        if ($nextOptional->prompt_path_step > $nextRequired->prompt_path_step ||
            $nextRequired->prompt_path_step >= ($this->completed_prompts + 1)) {
            return $nextRequired;
        }
        if (($nextRequired->prompt_path_step - $nextOptional->prompt_path_step) == 1) {
            return $nextOptional;
        }
        return $path->prompts()
            ->where([
                [ 'repeatable', '=', true ],
                [ 'prompt_path_step', '>', $this->completed_prompts ],
                [ 'prompt_path_step', '<', $nextRequired->prompt_path_step ]
            ])
            ->inRandomOrder()
            ->first();
    }

    public function getNextSegment()
    {
        $prompt = $this->current_prompt;
        $completed = $this->completed_segments;
        if ($completed == 0) {
            return $prompt->prompt_segments()->where('prompt_segment_order', 1)->first();
        }
        $next = $completed + 1;
        $segment = $prompt->prompt_segments()->where('prompt_segment_order', $next)->first();
        if (!empty($segment)) {
            return $segment;
        }
        $this->completed_segments = 0;
        $this->current_prompt()->dissociate();
        $this->save();
        $nextPrompt = $this->getNextPrompt();
        Log::debug(__METHOD__);
        Log::debug($nextPrompt);
        if (empty($nextPrompt)) {
            return false;
        }
        $segment = $nextPrompt->prompt_segments()->where('prompt_segment_order', 1)->first();
        return $segment;
    }

    public function readyForNextPrompt()
    {
        Log::debug(__METHOD__.": ".__LINE__);
        $operator = $this->operator;
        $preferences = $operator->preferences()->where('type', 'frequency')->first();
        if (empty($preferences)) {
            $prefer = '3 per week';
        } else {
            $prefer = $preferences->name;
        }
        $mostRecent = $operator->getLastCompletedPromptResponse();
        if (empty($mostRecent)) {
            return true;
        }
        $interval = $mostRecent->updated_at->diffInDays(now());
        Log::debug("readyForNextPrompt diff in days $interval");
        $target = $operator->frequencies[$prefer]['optimal_days_between_prompts'];
        Log::debug(__METHOD__.": interval $interval, target $target");
        if ($interval < $target) return false;
        return true;
    }
}
