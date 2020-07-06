<?php

namespace App\Objects;

use App\Objects\Archetypes\Question;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;

class PromptSegment extends Question
{
    use SoftDeletes;

    public $accessoryStruct = [
        'text' => [ 'type' => 'plain_text', 'text' => ''],
        'value' => '',
        'type' => '',
        'options' => [],
        'key' => []
    ];
    public $elements = [];

    public $fillable = [
        'segment_title', 'segment_text', 'segment_type', 'segment_imageUrl', 'segment_url',
        'segment_accessory', 'segment_elements', 'prompt_segment_order', 'accessory_type' ];
    protected $casts = [
        'segment_accessory' => 'array',
        'segment_elements' => 'array',
        'segment_key' => 'array'
    ];
    protected $attributes = [
        'segment_type' => 'section',
        'prompt_segment_order' => 1,
        'accessory_type' => 'info'
    ];

    public function __construct(array $attributes = [])
    {
        $this->segment_accessory = $this->accessoryStruct;
        parent::__construct($attributes);
    }

    public function options()
    {
        return $this->hasMany('App\Objects\PromptSegmentOption', 'question_id');
    }

    public function prompt()
    {
        return $this->belongsTo('App\Objects\Prompt');
    }

    public function getAccessory()
    {
        $accessory = $this->accessory;
        if (!$accessory) {
            $accessory = $this->accessoryStruct;
        }
        return $accessory;
    }

    public function getAccessoryOptionsString()
    {
        $accessory = $this->getAccessory();
        $options = $accessory['options'];
        $string = implode(',', $options);

        return strlen($string) < 2 ? '' : $string;
    }

    public function getOrderedSegmentTitles()
    {
        $prompt = $this->prompt;
        $segments = $prompt->ordered_segments;
        Log::debug(__METHOD__.": prompt->ordered_segments");
        $ordered = [];
        $i = 0;
        foreach ($segments as $segment) {
            Log::debug("$segment->segment_title: $segment->prompt_segment_order");
            $i++;
            $update = $i != $segment->prompt_segment_order ? true : false;
            $ordered[$i] = $segment->segment_title;
            if ($update) {
                $segment->prompt_segment_order = $i;
                $segment->save();
            }
        }
        return $ordered;
    }

    public function updatePromptSegmentOrder($order)
    {
        $ordered = $this->getOrderedSegmentTitles();
        if ($order == $this->prompt_segment_order) return true;
        if (!array_key_exists($order, $ordered)) {
            Log::debug(__CLASS__.__METHOD__." provided invalid order option = $order");
            return false;
        }
        if ($order > $this->prompt_segment_order + 1 || $order < $this->prompt_segment_order - 1) {
            Log::debug(__CLASS__.__METHOD__." provided invalid order option more than one step different than current order.");
            return false;
        }
        $prompt = $this->prompt;
        $swapSegment = $prompt->prompt_segments()->where('prompt_segment_order', $order)->first();
        Log::debug("Swap order with segment ".$swapSegment->segment_title);
        $swapSegment->prompt_segment_order = $this->prompt_segment_order;
        $swapSegment->save();
        $this->prompt_segment_order = $order;
        $this->save();
        return true;
    }

    public function save($options = [])
    {
        parent::save($options);
    }

    public function getSegmentAnswersString()
    {
        $key = $this->key;
        return strlen($key) < 2 ? '' : implode(',', $key);
    }

    public function createAccessory($type, $options, $text, $value)
    {
        $accessory = $this->accessoryStruct;
        $accessory['type'] =  $type;
        switch($type) {
            case 'checkboxes': // deliberate cascade
            case 'radio_buttons':
                $accessory['options'] = [];
                foreach ($options as $option=>$key) {
                    $accessory['key'][$option] = $key;
                    $accessory['options'][] = [
                        'value' => $option,
                        'text' => [
                            'type' => 'plain_text',
                            'text' => $option
                        ]
                    ];
                }
                break;
            case 'info':
            case 'button':
                $accessory['text'] = $text;
                $accessory['value'] = $value;
                break;
        }
        return $accessory;
    }
}
