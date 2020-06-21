<?php

namespace App\Objects;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;
use phpDocumentor\Reflection\Types\Parent_;

class PromptSegment extends Model
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

    protected $fillable = [
        'segment_title', 'segment_text', 'segment_type', 'segment_imageUrl', 'segment_url',
        'segment_accessory', 'segment_elements', 'prompt_segment_order' ];
    protected $casts = [ 'segment_accessory' => 'array', 'segment_elements' => 'array', 'segment_key' => 'array' ];
    protected $attributes = [ 'segment_type' => 'section', 'prompt_segment_order' => 1 ];

    public function __construct(array $attributes = [])
    {
        $this->segment_accessory = $this->accessoryStruct;
        parent::__construct($attributes);
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

    public function moveOrderEarlier()
    {
        $currentOrder = (int) $this->prompt_segment_order;
        Log::debug(__METHOD__.': current prompt segment order: '.$currentOrder);
        $newOrder = (int) $currentOrder - 1;
        Log::debug(__METHOD__.': attempting to set new segment order: '.$newOrder);
        if ($newOrder < 1 || $newOrder > $this->prompt->prompt_segments_count) {
            Log::debug(__METHOD__.": $newOrder either < 1 or > limit: ".$this->prompt->prompt_segments_count);
            return false;
        }
        return $this->updatePromptSegmentOrder($newOrder);
    }

    public function moveOrderLater()
    {
        Log::debug(__METHOD__.': current prompt segment order: '.$this->prompt_segment_order);
        $order = (int) $this->prompt_segment_order + 1;
        if ($order < 1 || $order > $this->prompt->prompt_segments_count) {
            return false;
        }
        return $this->updatePromptSegmentOrder($order);
    }

    public function updatePromptSegmentOrder($order)
    {
        Log::debug(__CLASS__.': '.__METHOD__.': with order = '.$order);
        if ($order == $this->prompt_segment_order) return true;
        $up = $order > $this->prompt_segment_order ? false : true;
        $options = $this->prompt->getSegmentOrderOptions(false);
        if (!array_key_exists($order, $options)) {
            Log::debug(__CLASS__.__METHOD__." provided invalid order option = $order");
            return false;
        }
        $segments = $this->prompt->ordered_segments;
        foreach ($segments as $segment) {
            if ($segment->id == $this->id) {
                $segment->prompt_segment_order = $order;
                $segment->save();
                continue;
            }
            if ($segment->prompt_segment_order == $order) {
                $shift = $up ? $order + 1 : $order - 1;
                $segment->prompt_segment_order = $shift;
                $segment->save();
                continue;
            }
            if ($segment->prompt_segment_order > $order) {
                $segment->prompt_segment_order = $segment->prompt_segment_order + 1;
                $segment->save();
            }
        }
        return true;
    }
    public function save($options = [])
    {
        Log::debug(__CLASS__." save()");
        Log::debug("title: ".$this->segment_title);
        Log::debug("order: ".$this->prompt_segment_order);
        parent::save($options);
    }

    public function getSegmentAnswersString()
    {
        $key = $this->key;
        $string = implode(',', $key);

        return strlen($string) < 2 ? '' : $string;
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
            case 'button':
                $accessory['text'] = $text;
                $accessory['value'] = $value;
                break;
        }
        return $accessory;
    }
}
