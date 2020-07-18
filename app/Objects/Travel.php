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
            ->orderByDesc('updated_at')
            ->first();
        if (empty($last)) return [];
        $segment_id = $last->question_id;
        return PromptSegment::find($segment_id);
    }

    public function readyForNextPrompt()
    {
        $operator = $this->operator;
        $mostRecent = $this->operator->travels()->orderBy('updated_at', 'desc')->first();
        $preferences = $operator->preferences()->where('type', 'frequency')->first();
        if (empty($preferences)) {
            $prefer = '3 per week';
        } else {
            $prefer = $preferences->name;
        }
        $interval = $mostRecent->updated_at->diffInDays(now());
        Log::debug("readyForNextPrompt diff in days $interval");
        $target = $operator->frequencies[$prefer]['optimal_days_between_prompts'];
        Log::debug(__METHOD__.": interval $interval, target $target");
        if ($interval <= $target) return false;
        return true;
    }
}
