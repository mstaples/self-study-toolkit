<?php

namespace App\Objects;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;
use App\Objects\PromptSegment;

class Prompt extends Model
{
    use SoftDeletes;
    private $prompt_path_step, $prompt_title, $repeatable, $optional;

    protected $fillable = [ 'prompt_path_step', 'prompt_title', 'repeatable', 'optional' ];

    protected $casts = [
        'prompt_segments' => 'array',
        'response_options' => 'array'
    ];

    protected $attributes = [
        'prompt_path_step' => 0
    ];

    protected $withCount = ['prompt_segments'];

    public function prompt_path()
    {
        return $this->belongsTo('App\Objects\PromptPath');
    }

    public function prompt_segments()
    {
        return $this->hasMany('App\Objects\PromptSegment');
    }

    public function ordered_segments()
    {
        return $this->prompt_segments()->orderBy('prompt_segment_order', 'asc');
    }

    public function getSegmentOrderOptions($named = true)
    {
        $options = [ 1 => '1' ];
        $count = $this->prompt_segments_count;
        if ($count > 0) {
            $options = array_fill(1, $count, '');
            if (!$named) return $options;
            foreach ($this->ordered_segments as $segment) {
                $order = $segment->prompt_segment_order;
                if (!array_key_exists($order, $options) || $options[$order] != '') {
                    foreach ($options as $key => $option) {
                        if ($option === '') {
                            $options[$key] = $key . ' (' . $segment->segment_title . ')';
                            Log::debug("prompt segment -- ".$segment->segment_title." -- provided prompt_segment_order value $order, but that isn't an available option. Resetting order to $key");
                            $segment->prompt_segment_order = $key;
                            $segment->save();
                            continue 2;
                        }
                    }
                } else {
                    $options[$order] = $order . " (" . $segment->segment_title . ")";
                    Log::debug("prompt segment -- ".$segment->segment_title." -- provided prompt_segment_order value $order");
                }
            }
        }
        return $options;
    }

    // single source of truth for these names
    public function getSegmentTypes()
    {
        return [
            'information' => "Information segments contain text / or a URL.",
            'image' => "Image segments feature a main image along with text.",
            'interaction' => "Interaction segments gather input from the operator."
        ];
    }

    public function setPromptStep($step)
    {
        if ($step == $this->prompt_path_step) {
            Log::debug(__METHOD__.": Prompt already set to step $step. No change made.");
            Log::debug(__METHOD__.": title ".$this->prompt_title);
            return;
        }
        $prompts = $this->prompt_path->prompts;
        foreach ($prompts as $prompt) {
            if ($prompt->id == $this->id) {
                $prompt->prompt_path_step = $step;
                $prompt->save();
                continue;
            }
            if ($prompt->prompt_path_step >= $step) {
                $prompt->prompt_path_step = $prompt->prompt_path_step + 1;
                $prompt->save();
            }
        }
    }
}
